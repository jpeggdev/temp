<?php

namespace App\Services;

use App\Entity\Company;
use App\Exceptions\FileConverterException;
use App\Exceptions\PartialProcessingException;
use App\Importers\AbstractImporter;
use App\Importers\ImportFactory;
use App\Parsers\CsvRecordParser;
use App\Parsers\RecordParser;
use App\Repository\CompanyRepository;
use App\Repository\TradeRepository;
use App\ValueObjects\CompanyObject;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory as FileReader;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use ReflectionException;
use RuntimeException;

use function App\Functions\app_stringList;

readonly class MigrationService
{
    public function __construct(
        private FileConverter $fileConverter,
        private CompanyRepository $companyRepository,
        private EntityManagerInterface $entityManager,
        private TradeRepository $tradeRepository
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws FileConverterException
     * @throws \League\Csv\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws PartialProcessingException
     */
    public function migrate(
        string $companyIdentifier,
        string $inputFilePath,
        ?string $passedDataSource, //mailmanager
        string $dataType, //prospects
        array $options,
        ?int $limit = null,
        ?string $trade = null
    ): AbstractImporter {
        $company = $this->companyRepository->findActiveByIdentifierOrCreate($companyIdentifier);
        if (!$company instanceof Company) {
            throw new InvalidArgumentException(sprintf("The company '%s' could not be found.", $companyIdentifier));
        }

        if (
            !file_exists($inputFilePath) ||
            filesize($inputFilePath) === 0
        ) {
            $exception = sprintf(
                "[%s] is not a valid file path.",
                $inputFilePath
            );

            throw new InvalidArgumentException($exception, 1);
        }

        // Determine data-source
        $validDataSources = ImportFactory::getAvailableDataSources();

        if (
            $passedDataSource &&
            !in_array($passedDataSource, $validDataSources, true)
        ) {
            $exception = sprintf(
                "%s is not a valid data-source. Valid data-sources are [%s]",
                $passedDataSource,
                app_stringList($validDataSources)
            );

            throw new RuntimeException($exception, 1);
        }

        echo $company->getIdentifier()
        . ': '
        . date('Y-m-d H:i:s')
        . ' '
        . 'START: Converting DBF to CSV: '
        . $inputFilePath
        . PHP_EOL;

        $filePath = $this->getConvertedFilePath(
            $inputFilePath,
            $limit
        );

        echo $company->getIdentifier()
        . ': '
        . date('Y-m-d H:i:s')
        . ' '
        . 'DONE: Converting DBF to CSV: '
        . $filePath
        . PHP_EOL;

        echo $company->getIdentifier()
            . ': '
            . date('Y-m-d H:i:s')
            . ' '
            . 'START: Parsing Records: '
            . $filePath
            . PHP_EOL;

        // Import and Parse Raw Data
        if (FileConverter::isCsvFile($filePath)) {
            $recordParser = (new CsvRecordParser(
                $filePath
            ))->parseRecords(
                $limit
            );
        } else {
            $recordParser = (new RecordParser(
                $filePath
            ))->parseRecords(
                $limit
            );
        }

        echo $company->getIdentifier()
            . ': '
            . date('Y-m-d H:i:s')
            . ' '
            . 'DONE: Parsing Records: '
            . $filePath
            . PHP_EOL;

        $dataSource = $passedDataSource ?? ImportFactory::identifyDataSource(
            $inputFilePath,
            $recordParser->getHeaders()
        );

        if (!$dataSource) {
            $exception = 'Unable to determine the data-source.';
            throw new RuntimeException($exception, 1);
        }

        // Validate data-source, data-type
        $importFactory = new ImportFactory(
            $this->tradeRepository,
            $this->entityManager,
            $dataSource,
            $dataType
        );

        $companyObject = CompanyObject::fromEntity($company);

        // Instantiate Importer
        $import = $importFactory
            ->getImporter($companyObject)
            ->setOptions($options);
        if ($trade) {
            $import->setTrade($trade);
        }

        echo $company->getIdentifier()
        . ': '
        . date('Y-m-d H:i:s')
        . ' '
        . 'START: Extracting Records: '
        . $filePath
        . PHP_EOL;
        // Parse Raw Data Into Array of Objects

        $records = $importFactory
            ->getParser($companyObject)
            ->parse(
                $recordParser->getHeaders(),
                $recordParser->getRecords()
            )
            ->getRecords();

        echo $company->getIdentifier()
        . ': '
        . date('Y-m-d H:i:s')
        . ' '
        . 'DONE: Extracting Records: '
        . count($records)
        . PHP_EOL;

        if ($import instanceof AbstractImporter) {
            $import->import($records);
        } else {
            $exception = sprintf(
                "Unable to resolve importer for %s",
                get_class($import)
            );

            throw new RuntimeException($exception, 1);
        }
        return $import;
    }

    public function getConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }

    public function isFileValid(
        string $fileToTest,
        ?int $limit = null
    ): bool {
        $isValid = true;
        try {
            if (strtolower(pathinfo($fileToTest, PATHINFO_EXTENSION)) === 'xlsx') {
                FileReader::identify($fileToTest);
            } else {
                $convertedFileToTest = $this->getConvertedFilePath(
                    $fileToTest,
                    $limit
                );
                FileReader::identify($convertedFileToTest);
            }
        } catch (FileConverterException | Exception) {
            $isValid = false;
        }
        return $isValid;
    }

    /**
     * @param string $inputFilePath
     * @param int|null $limit
     * @return string
     * @throws FileConverterException
     */
    private function getConvertedFilePath(
        string $inputFilePath,
        ?int $limit = null
    ): string {
        $csvPath = $this->fileConverter->convertToCsv(
            $inputFilePath,
            $limit
        );

        return (
            $csvPath &&
            file_exists($csvPath) &&
            filesize($csvPath) > 0
        ) ? $csvPath : $inputFilePath;
    }
}
