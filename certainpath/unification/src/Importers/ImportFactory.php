<?php

namespace App\Importers;

use App\Parsers\AbstractParser;
use App\Repository\TradeRepository;
use App\ValueObjects\CompanyObject;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

use function App\Functions\app_stringList;

class ImportFactory
{
    private ?AbstractImporter $migrator = null;
    private ?AbstractParser $parser = null;

    /** @var array */
    private static array $migrators = [
        // General Data Importers
        'addresses' => \App\Importers\AddressImporter ::class,
        'customers' => \App\Importers\CustomerImporter ::class,
        'invoices' => \App\Importers\InvoiceImporter ::class,
        'prospects' => \App\Importers\ProspectImporter ::class,

        // DataSource Specific Importers
        /*'example' => [
            'addresses' => \App\Importers\Vendor\AddressImporter ::class,
            'customers' => \App\Importers\Vendor\CustomerImporter ::class,
        ],*/
    ];

    /** @var array */
    private static array $parsers = [
        'mailmanager' => [
            'prospects' => \App\Parsers\MailManager\ProspectParser::class,
            'customers' => \App\Parsers\MailManager\CustomerParser::class,
            'addresses' => \App\Parsers\MailManager\AddressParser::class,
        ],
        'mailmanagerlife' => [
            'prospects' => \App\Parsers\MailManagerLife\ProspectParser::class,
            'customers' => \App\Parsers\MailManagerLife\CustomerParser::class,
            'addresses' => \App\Parsers\MailManagerLife\AddressParser::class,
            'invoices' => \App\Parsers\MailManagerLife\InvoiceParser::class,
        ],
        'servicetitan' => [
            'prospects' => \App\Parsers\ServiceTitan\ProspectParser::class,
            'customers' => \App\Parsers\ServiceTitan\CustomerParser::class,
            'addresses' => \App\Parsers\ServiceTitan\AddressParser::class,
            'invoices' => \App\Parsers\ServiceTitan\InvoiceParser::class,
        ],
    ];

    /** @var string */
    private mixed $migratorClass;

    /** @var string */
    private mixed $parserClass;

    public function __construct(
        private readonly TradeRepository $tradeRepository,
        private readonly EntityManagerInterface $entityManager,
        string $dataSource = null,
        string $dataType = null
    ) {
        // Resolve Data Source Specific Parser Class
        if (!isset(self::$parsers[$dataSource])) {
            throw new InvalidArgumentException(
                sprintf(
                    "The data-source '%s' is not valid. Valid data-sources are: %s.",
                    $dataSource,
                    app_stringList(array_keys(self::$parsers))
                )
            );
        }

        if (!isset(self::$parsers[$dataSource][$dataType])) {
            throw new InvalidArgumentException(
                sprintf(
                    "The data-source '%s' does not support the '%s' data-type. Valid data-types are: %s.",
                    $dataSource,
                    $dataType,
                    app_stringList(array_keys(self::$parsers[$dataSource]))
                )
            );
        }

        $this->parserClass = self::$parsers[$dataSource][$dataType];

        if (
            isset(self::$migrators[$dataSource]) &&
            is_array(self::$migrators[$dataSource])
        ) {
            // Resolve Data Source Specific Migrator Class
            if (isset(self::$migrators[$dataSource][$dataType])) {
                $this->migratorClass = self::$migrators[$dataSource][$dataType];
            }
        }

        // Resolve General Migrator Class
        if (empty($this->migratorClass)) {
            if (!isset(self::$migrators[$dataType])) {
                throw new InvalidArgumentException(
                    sprintf(
                        "The data-type '%s' is not valid. Valid data-types are: %s.",
                        $dataType,
                        app_stringList(array_keys(self::$parsers))
                    )
                );
            }

            $this->migratorClass = self::$migrators[$dataType];
        }
    }

    /**
     * @throws ReflectionException
     */
    public function getParser(CompanyObject $company): AbstractParser
    {
        if (!$this->parser instanceof AbstractParser) {
            if (!class_exists($this->parserClass)) {
                throw new RuntimeException(
                    sprintf(
                        "The parser class '%s' could not be found.",
                        $this->parserClass
                    )
                );
            }

            $this->parser = (new ReflectionClass(
                $this->parserClass
            ))->newInstance(
                $company,
                $this->entityManager,
                $this->tradeRepository
            );
        }

        return $this->parser;
    }

    public function getImporter(CompanyObject $company): AbstractImporter
    {
        if (!$this->migrator instanceof AbstractImporter) {
            if (!class_exists($this->migratorClass)) {
                throw new RuntimeException(
                    sprintf("The importer class '%s' could not be found.", $this->migratorClass)
                );
            }

            $this->migrator = (new ReflectionClass(
                $this->migratorClass
            ))->newInstance(
                $company,
                $this->entityManager,
                $this->tradeRepository
            );
        }

        return $this->migrator;
    }

    public static function getAvailableDataSources(): array
    {
        return array_keys(self::$parsers);
    }

    public static function listAllParsers(): array
    {
        $arr = [ ];
        foreach (self::$parsers as $group) {
            foreach ($group as $parser) {
                $arr[] = $parser;
            }
        }

        return $arr;
    }

    public static function identifyDataSource(string $filePath, array $headers): ?string
    {
        foreach (self::listAllParsers() as $parser) {
            if (
                $parser::hasMatchingHeaders($headers) &&
                $parser::hasMatchingFileName(basename($filePath))
            ) {
                return $parser::getSourceName();
            }
        }

        return null;
    }
}
