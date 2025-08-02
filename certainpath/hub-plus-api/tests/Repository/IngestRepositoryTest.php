<?php

namespace App\Tests\Repository;

use App\Entity\FieldServiceSoftware;
use App\Entity\Trade;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\CustomerRecord;
use App\ValueObject\InvoiceRecord;
use App\ValueObject\InvoiceRecordMap;
use App\ValueObject\MemberRecord;
use App\ValueObject\MemberRecordMap;
use App\ValueObject\TabularFile;
use Doctrine\DBAL\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class IngestRepositoryTest extends AbstractKernelTestCase
{
    public function setUp(): void
    {
        $this->doInitializeTrades = true;
        $this->doInitializeSoftware = true;
        parent::setUp();
    }
    /**
     * @throws Exception
     */
    public function testIngestRepositoryDatabaseAndColumns(): void
    {
        $repo = $this->ingestRepository;
        self::assertNotNull($repo);

        $name = $repo->getDatabaseName();
        self::assertSame(
            'unification_ingest_generic_test',
            $name
        );

        $invoiceColumns = $repo->getTableColumns(
            'invoices_stream'
        );
        self::assertArrayHasKey(
            'customer_id',
            $invoiceColumns
        );
        self::assertArrayHasKey(
            'invoice_number',
            $invoiceColumns
        );
        self::assertArrayHasKey(
            'job_number',
            $invoiceColumns
        );

        $memberColumns = $repo->getTableColumns(
            'members_stream'
        );
        self::assertArrayHasKey(
            'customer_id',
            $memberColumns
        );
        self::assertArrayHasKey(
            'customer_name',
            $memberColumns
        );
        self::assertArrayHasKey(
            'customer_first_name',
            $memberColumns
        );
        self::assertArrayHasKey(
            'customer_last_name',
            $memberColumns
        );
    }

    /**
     * @throws CouldNotReadSheet
     * @throws Exception
     * @throws FieldsAreMissing
     * @throws IOException
     * @throws NoFilePathWasProvided
     * @throws ReaderNotOpenedException
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \JsonException
     * @throws \League\Csv\Exception
     */
    public function testInsertInvoiceRecords(): void
    {
        $this->insertRecords(
            __DIR__.'/../Files/SM000250-service-titan-jobs.xlsx',
            'SM000250',
            'invoices_stream',
            InvoiceRecord::class,
            'insertInvoiceRecord',
            'getInvoiceRecordsForTenant',
            __DIR__.'/../Files/SM000250-service-titan-jobs-mapped.json'
        );
    }

    /**
     * @throws IOException
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws UnsupportedFileTypeException
     * @throws SyntaxError
     * @throws \League\Csv\Exception
     * @throws Exception
     * @throws \JsonException
     * @throws FieldsAreMissing
     */
    public function testInsertMemberRecords(): void
    {
        $this->insertRecords(
            __DIR__.'/../Files/SM000250-service-titan-members.xlsx',
            'SM000250',
            'members_stream',
            MemberRecord::class,
            'insertMemberRecord',
            'getMemberRecordsForTenant',
            __DIR__.'/../Files/SM000250-service-titan-members-mapped.json'
        );
    }

    /**
     * @throws IOException
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws UnsupportedFileTypeException
     * @throws SyntaxError
     * @throws \League\Csv\Exception
     * @throws Exception
     * @throws \JsonException
     * @throws FieldsAreMissing
     * @throws NoFilePathWasProvided
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     */
    private function insertRecords(
        string $filePath,
        string $tenant,
        string $tableName,
        string $recordClass,
        string $insertMethod,
        string $getRecordsMethod,
        string $jsonFilePath,
    ): void {
        self::assertFileExists($filePath);
        if ('invoices_stream' === $tableName) {
            $tabularFile = TabularFile::fromExcelOrCsvFile(
                new InvoiceRecordMap(),
                $filePath,
            );
        } else {
            $tabularFile = TabularFile::fromExcelOrCsvFile(
                new MemberRecordMap(),
                $filePath,
            );
        }
        $count = 0;
        $rows = $tabularFile->getRowIteratorForColumns(
            $tabularFile->getHeadersAsArray()
        );
        $trade = $this->tradeRepository->getTrade(
            Trade::hvac()
        );
        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );
        $tabularRecords = [];
        foreach ($rows as $row) {
            ++$count;
            $row['Tenant'] = $tenant;
            $row['trade'] = $trade->getLongName();
            $row['software'] = $software->getName();
            $row['version'] = time();
            $row['hub_plus_import_id'] = time();
            if ('invoices_stream' === $tableName) {
                $record = InvoiceRecord::fromTabularRecord($row);
            } else {
                $record = MemberRecord::fromTabularRecord($row);
            }
            try {
                if ($record instanceof CustomerRecord) {
                    $record->processCustomerNames();
                    if ($record instanceof MemberRecord) {
                        $record->processMembershipType();
                    }
                }
            } catch (FieldsAreMissing $e) {
                echo $e->getMessage().PHP_EOL;
                continue;
            }
            $arrayRecord = $record->toArray();
            unset(
                $arrayRecord['hub_plus_import_id'],
                $arrayRecord['version']
            );
            $tabularRecords[] = $arrayRecord;
            if ($record instanceof InvoiceRecord) {
                $this->ingestRepository->insertInvoiceRecord($record);
            } elseif ($record instanceof MemberRecord) {
                $this->ingestRepository->insertMemberRecord($record);
            }
            if ($count >= 10) {
                break;
            }
        }
        if ('getInvoiceRecordsForTenant' === $getRecordsMethod) {
            $savedRecords = $this->ingestRepository->getInvoiceRecordsForTenant($tenant);
        } else {
            $savedRecords = $this->ingestRepository->getMemberRecordsForTenant($tenant);
        }
        self::assertCount(10, $savedRecords);
        $databaseRecords = [];
        foreach ($savedRecords as $record) {
            self::assertSame(
                $tenant,
                $record->tenant
            );
            self::assertInstanceOf(
                $recordClass,
                $record
            );

            $arrayRecord = $record->toArray();
            unset(
                $arrayRecord['hub_plus_import_id'],
                $arrayRecord['version']
            );
            $databaseRecords[] = $arrayRecord;
        }
        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $tenant,
            $tableName
        );
        self::assertSame(
            $tabularRecords,
            $databaseRecords
        );
        self::assertFileExists($jsonFilePath);
        $jsonContents = file_get_contents($jsonFilePath);
        $expectedRecords = json_decode($jsonContents, true, 512, JSON_THROW_ON_ERROR);
        foreach ($expectedRecords as &$expectedRecord) {
            unset(
                $expectedRecord['version'],
                $expectedRecord
            );
        }
        self::assertSame(
            $expectedRecords,
            $databaseRecords
        );
    }
}
