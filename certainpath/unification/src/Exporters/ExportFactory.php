<?php

namespace App\Exporters;

use App\Client\FileClient;
use App\Parsers\MailManager\MailManagerParser;
use App\ValueObjects\CompanyObject;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

use function App\Functions\{
    app_stringList
};

/**
 * @property array $exporter
 */
class ExportFactory
{
    public static array $dataTargets = [
        MailManagerParser::class,
    ];

    /** @var AbstractExporter */
    private ?AbstractExporter $exporter = null;

    /** @var array */
    private array $exporters = [
        // DataSource Specific Exporters
        'mailmanager' => [
            'prospects' => \App\Exporters\MailManager\ProspectExporter ::class,
        ]
    ];
    /**
     * @var array|mixed
     */
    private mixed $exporterClass;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileClient $fileClient,
        string $dataTarget = null,
        string $dataType = null
    ) {
        if (
            !isset($this->exporters[$dataTarget]) ||
            !is_array($this->exporters[$dataTarget])
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    "The data-target '%s' is not valid. Valid data-target are: %s.",
                    $dataType,
                    app_stringList(array_keys($this->exporters))
                )
            );
        }

        // Resolve Data Source Specific Exporter Class
        if (isset($this->exporters[$dataTarget][$dataType])) {
            $this->exporterClass = $this->exporters[$dataTarget][$dataType];
        }

        // Resolve General Migrator Class
        if (empty($this->exporterClass)) {
            if (!isset($this->exporters[$dataType])) {
                throw new InvalidArgumentException(
                    sprintf(
                        "The data-type '%s' is not valid. Valid data-types are: %s.",
                        $dataType,
                        app_stringList(array_keys($this->exporters[$dataTarget]))
                    )
                );
            }
        }
    }

    public function getExporter(CompanyObject $companyObject): AbstractExporter
    {
        if (!$this->exporter instanceof AbstractExporter) {
            if (!class_exists($this->exporterClass)) {
                throw new RuntimeException(
                    sprintf("The importer class '%s' could not be found.", $this->exporterClass)
                );
            }

            $this->exporter = (new ReflectionClass(
                $this->exporterClass
            ))->newInstance(
                $companyObject,
                $this->entityManager,
                $this->fileClient
            );
        }

        return $this->exporter;
    }
}
