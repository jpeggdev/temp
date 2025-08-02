<?php

namespace App;

use App\Services\FileConverter;
use App\Services\FileWriter;
use App\ValueObjects\AbstractObject;
use App\ValueObjects\CompanyObject;
use App\ValueObjects\SequencedObject;
use App\ValueObjects\UpdatableInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\CurlHandler as GuzzleCurlHandler;
use GuzzleHttp\Handler\CurlFactory as GuzzleCurlFactory;
use GuzzleHttp\HandlerStack as GuzzleHandlerStack;
use RuntimeException;

abstract class AbstractMigrator
{
    protected CompanyObject $companyObject;
    protected GuzzleClient $guzzleClient;
    protected ?FileWriter $fileWriter = null;
    protected ?FileConverter $fileConverter = null;
    protected array $records = [];
    protected array $options = [];
    protected string $runTime = '0.0000';
    protected int $recordCount = 0;
    protected int $startCount = 0;
    protected int $endCount = 0;
    protected int $exportCount = 0;
    protected int $insertCount = 0;
    protected int $invalidCount = 0;
    protected bool $dryRun = false;
    protected bool $showInvalid = false;
    protected bool $updateRecords = false;
    protected bool $verbose = false;
    protected array $supportedOptions;
    protected string $outputString = '';

    private const SUPPORTED_OPTIONS = [
        'company',
        'action',
        'dry-run',
        'show-invalid',
        'verbose',
    ];
    private ?string $trade = null;


    public function __construct(
        CompanyObject $companyObject,
        protected EntityManagerInterface $entityManager
    ) {
        $handler = new GuzzleCurlHandler([
            'handle_factory' => new GuzzleCurlFactory(1)
        ]);

        $client = new GuzzleClient([
            'handler' => GuzzleHandlerStack::create($handler)
        ]);

        $this->setGuzzleClient($client);

        $this->companyObject = $companyObject;

        // Set base supported options
        // Child class constructors will add to or remove from this array as needed.
        $this->supportedOptions = self::SUPPORTED_OPTIONS;
    }

    public function setTrade(?string $trade): void
    {
        $this->trade = $trade;
    }

    public function getTrade(): ?string
    {
        return $this->trade;
    }

    public function setGuzzleClient(GuzzleClient $guzzleClient): self
    {
        $this->guzzleClient = $guzzleClient;

        return $this;
    }

    public function getCompanyObject(): ?CompanyObject
    {
        return $this->companyObject;
    }
    public function getRecordCount(): int
    {
        return $this->recordCount;
    }

    public function getStartCount(): int
    {
        return $this->startCount;
    }

    public function getEndCount(): int
    {
        return $this->endCount;
    }

    public function getImportCount(): int
    {
        return ($this->endCount - $this->startCount);
    }

    public function getExportCount(): int
    {
        return $this->exportCount;
    }

    public function getInsertCount(): int
    {
        return $this->insertCount;
    }

    public function getInvalidCount(): int
    {
        return $this->invalidCount;
    }

    public function getRunTime(): string
    {
        return $this->runTime;
    }

    public function getOutputString(): string
    {
        return $this->outputString;
    }

    public function setOption(string $option, $value): self
    {
        if (!in_array($option, $this->getSupportedOptions(), true)) {
            throw new RuntimeException(
                "Unsupported argument specified for this command:\n$option",
                1
            );
        }

        return $this->hydrateOptions(
            array_merge(
                $this->options,
                [$option => $value]
            )
        );
    }

    public function setFileWriter(FileWriter $fileWriter): static
    {
        $this->fileWriter = $fileWriter;

        return $this;
    }

    public function setFileConverter(FileConverter $fileConverter): static
    {
        $this->fileConverter = $fileConverter;

        return $this;
    }

    public function setOptions(array $options): self
    {
        $unsupportedOptions = array_keys(
            array_diff_key(
                array_filter($options),
                array_flip($this->getSupportedOptions())
            )
        );

        if ($unsupportedOptions) {
            throw new RuntimeException(
                "Unsupported option(s) specified for this command:\n" .
                implode(", ", $unsupportedOptions),
                1
            );
        }

        return $this->hydrateOptions($options);
    }

    public function getOption(string $option): ?string
    {
        return $this->options[$option] ?? null;
    }

    public function getSupportedOptions(): array
    {
        return $this->supportedOptions;
    }

    protected function generateRecordId(AbstractObject $record): bool
    {
        if ($record->hasId()) {
            return false;
        }

        $sqlSelectNextval = $this->entityManager->getConnection()
            ->getDatabasePlatform()
            ?->getSequenceNextValSQL(
                $record->getTableSequence()
            );

        $recordId = $this->entityManager->getConnection()->fetchOne(
            $sqlSelectNextval
        );

        $record->setId($recordId);

        return true;
    }

    protected function saveRecord(AbstractObject $record): bool
    {
        try {
            if (!$record->isValid()) {
                if ($this->showInvalid) {
                    print_r($record);
                    echo "\r\n";
                }

                ++$this->invalidCount;

                return false;
            }

            $types = [];
            foreach ($record->toArray() as $key => $value) {
                if (is_bool($value)) {
                    $types[$key] = Types::BOOLEAN;
                }
            }

            // If the record has no ID, generate one and insert it as a new record.
            if (!$record->hasId()) {
                $this->generateRecordId($record);
                $this->generateSequence($record);

                if (!$this->dryRun) {
                    $this->entityManager->getConnection()->insert(
                        $record->getTableName(),
                        $record->toArray(),
                        $types
                    );
                    ++$this->insertCount;
                }

                return true;
            }

            // If the record has an ID, check if updating records is enabled and if the record is updatable.
            if (
                !$this->updateRecords ||
                !($record instanceof UpdatableInterface)
            ) {
                return false;
            }

            // Reduce the recordArray to updatable values only.
            $updateArr = $record->toUpdateArray();

            $query = sprintf(
                'SELECT %s FROM %s
                            WHERE %s = :record_id
                            ORDER BY :order_by ASC
                            LIMIT 1',
                implode(',', array_keys($updateArr)),
                $record->getTableName(),
                $record->getPrimaryKeyColumn()
            );

            // Fetch the existing record's updatable values.
            $existing = $this->entityManager->getConnection()->fetchAssociative(
                $query,
                [
                    'record_id' => $record->getId(),
                    'order_by' => $record->getPrimaryKeyColumn(),
                ]
            );

            // If the existing record's updatable values are the same as the new record's updatable values, return true.
            if ($updateArr === $existing) {
                return true;
            }

            // If the record has an updated_at column and it is empty, set it to the current date.
            if (
                array_key_exists('updated', array_flip($record->getUpdatableProperties())) &&
                empty($updateArr['updated'])
            ) {
                $updateArr['updated'] = $record->formatDate(date_create());
            }

            // Only update the record if we are not in dry-run mode.
            if ($this->dryRun) {
                return true;
            }

            // Update the record.
            $this->updateCount += $this->entityManager->getConnection()->update(
                $record->getTableName(),
                $updateArr,
                [
                    $record->getPrimaryKeyColumn() => $record->getId()
                ],
                array_fill(0, count($updateArr), 'text')
            );
        } catch (Exception $e) {
            echo 'Record: ' . $record->getId() . ': ' . $e->getMessage() . PHP_EOL;
        }
        return true;
    }

    protected function generateSequence(AbstractObject $record): bool
    {
        if (!$record instanceof SequencedObject) {
            return false;
        }

        if ($record->hasSequence()) {
            return false;
        }

        $sqlGenerateSequence = trim("
            SELECT generate_sequence(:company, :sequence)
        ");

        $record->_sequence = $this->entityManager->getConnection()->fetchOne($sqlGenerateSequence, [
            'company' => $record->getCompanyIdentifier(),
            'sequence' => $record->getSequence()
        ]);

        return true;
    }

    protected function initialize(array $records = []): self
    {
        $this->records = $records;
        $this->recordCount = count($records);

        $this->startCount = 0;
        $this->endCount = 0;
        $this->invalidCount = 0;
        $this->insertCount = 0;
        $this->exportCount = 0;

        return $this;
    }

    protected function getFileResource(string $filePath, string $access = 'cb+')
    {
        $resource = fopen($filePath, $access);

        if ($resource === false) {
            throw new RuntimeException("Unable to access file.", 1);
        }

        return $resource;
    }

    protected function randSleep($weight = 25)
    {
        if (random_int(1, $weight) === 1) {
            $sleep = random_int(2, 5);
            echo "Pausing for $sleep seconds.\r\n";
            sleep($sleep);
        }
    }

    private function hydrateOptions(array $options): self
    {
        $this->options = array_filter($options);

        $this->dryRun = !empty($this->options['dry-run']);
        $this->showInvalid = !empty($this->options['show-invalid']);
        $this->verbose = !empty($this->options['verbose']);

        return $this;
    }
}
