<?php

namespace App\Tests\Service;

use App\Entity\FieldServiceSoftware;
use App\Entity\Trade;
use App\Exception\CompanyProcessDispatchException;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Tests\AbstractKernelTestCase;
use Doctrine\DBAL\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FieldServicesUploadServiceTest extends AbstractKernelTestCase
{
    public function setUp(): void
    {
        $this->doInitializeTrades = true;
        $this->doInitializeSoftware = true;
        parent::setUp();
    }
    /**
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \DateMalformedStringException
     * @throws CompanyProcessDispatchException
     * @throws ServerExceptionInterface
     * @throws \League\Csv\Exception
     * @throws NoFilePathWasProvided
     * @throws Exception
     * @throws IOException
     * @throws RedirectionExceptionInterface
     * @throws UnavailableStream
     * @throws TransportExceptionInterface
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     */
    public function testErrorThrowingExcelFiles(): void
    {
        $service = $this->getFieldServicesUploadService();
        $companyIngestion = $this->getStochasticCompanyIngestionService();
        self::assertNotNull($companyIngestion);
        $companyIngestion->updateAllCompaniesFromStochasticRoster();
        $testCompany = $this->companyRepository->findOneByIdentifier('SM000250');

        $trade = $this->tradeRepository->getTrade(
            Trade::hvac()
        );
        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );

        // Create a mock import ID
        $importId = 1;

        $service->processJobsOrInvoiceFile(
            __DIR__.'/../Files/invoice-list-throws-sheet-related-error.xlsx',
            $testCompany,
            $trade,
            $software,
            101
        );

        $savedInvoices = $this->ingestRepository->getInvoiceRecordsForTenant(
            $testCompany->getIntacctId()
        );
        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $testCompany->getIntacctId(),
            'invoices_stream'
        );

        self::assertNotEmpty(
            $savedInvoices
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
     * @throws CompanyProcessDispatchException
     * @throws \DateMalformedStringException
     * @throws \League\Csv\Exception
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testCorruptedInvoiceFile(): void
    {
        $service = $this->getFieldServicesUploadService();
        $companyIngestion = $this->getStochasticCompanyIngestionService();
        self::assertNotNull($companyIngestion);
        $companyIngestion->updateAllCompaniesFromStochasticRoster();
        $testCompany = $this->companyRepository->findOneByIdentifier('SM000250');

        $trade = $this->tradeRepository->getTrade(
            Trade::hvac()
        );
        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );

        // Create a mock import ID
        $importId = 2;

        $service->processJobsOrInvoiceFile(
            __DIR__.'/../Files/corrupted-invoice-file.csv',
            $testCompany,
            $trade,
            $software,
            101
        );

        $savedInvoices = $this->ingestRepository->getInvoiceRecordsForTenant($testCompany->getIntacctId());
        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $testCompany->getIntacctId(),
            'invoices_stream'
        );
        self::assertGreaterThan(10, count($savedInvoices));

        self::assertSame(
            '0',
            $savedInvoices[0]->total
        );
        self::assertSame(
            '499.5',
            $savedInvoices[1]->total
        );
        self::assertSame(
            '170',
            $savedInvoices[2]->total
        );
        self::assertSame(
            '343',
            $savedInvoices[3]->total
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CompanyProcessDispatchException
     * @throws CouldNotReadSheet
     * @throws DecodingExceptionInterface
     * @throws Exception
     * @throws FieldsAreMissing
     * @throws IOException
     * @throws NoFilePathWasProvided
     * @throws ReaderNotOpenedException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \DateMalformedStringException
     * @throws \League\Csv\Exception
     * @throws ExcelFileIsCorrupted
     * @throws \JsonException
     */
    public function testProcessExcelJobsOrInvoiceFile(): void
    {
        $service = $this->getFieldServicesUploadService();
        self::assertNotNull($service);

        $companyIngestion = $this->getStochasticCompanyIngestionService();
        self::assertNotNull($companyIngestion);
        $companyIngestion->updateAllCompaniesFromStochasticRoster();
        $testCompany = $this->companyRepository->findOneByIdentifier('SM000250');
        self::assertNotNull($testCompany);

        $trade = $this->tradeRepository->getTrade(
            Trade::hvac()
        );
        self::assertNotNull($trade);
        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );
        self::assertNotNull($software);

        // Create a mock import ID
        $importId = 3;

        // 14 minutes against localhost database for 152,767 records
        // using single record insert method.
        // same amount of time for 1000 record FAKE-batch insert method.

        // 12 minutes with 1000 record REAL-batch insert method and transaction wrap.
        // removing transaction wrap did not help. 13 minutes now.

        // 12:35 minutes with 2000 record REAL-batch insert method and transaction wrap.

        // 1:45 minutes replacing openspout with avadim/fast-excel-reader

        // 2:10 minutes from optimizing getHeadersFromExcel() with $sheet->nextRow

        // 1:02 minutes from optimizing headers from AbstractRecordMap

        // 2024.02.18 -- Time: 01:09.588, Memory: 1.04 GB
        // Still using avadim/fast-excel-reader but refactored
        // to support openspout and phpoffice/excel.

        // Tried switching back to OpenSpout and:
        // Time: 11:59.018, Memory: 976.53 MB

        //        $service->processJobsOrInvoiceFile(
        //            __DIR__.'/../Files/SM000250-service-titan-jobs.xlsx',
        //            $testCompany,
        //            $trade,
        //            $software,
        //            101
        //        );
        $service->processJobsOrInvoiceFile(
            __DIR__.'/../Files/SM000250-service-titan-jobs-small.xlsx',
            $testCompany,
            $trade,
            $software,
            101
        );

        $savedJobs = $this->ingestRepository->getInvoiceRecordsForTenant($testCompany->getIntacctId());
        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $testCompany->getIntacctId(),
            'invoices_stream'
        );
        self::assertCount(10, $savedJobs);
        // og: 152767
        // - 153389
        // - 152849
        // - 152752
        // - 170423
        // self::assertCount(10, $savedJobs);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CompanyProcessDispatchException
     * @throws CouldNotReadSheet
     * @throws DecodingExceptionInterface
     * @throws Exception
     * @throws FieldsAreMissing
     * @throws IOException
     * @throws NoFilePathWasProvided
     * @throws ReaderNotOpenedException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \DateMalformedStringException
     * @throws \League\Csv\Exception
     */
    public function testProcessCsvJobsOrInvoiceFile(): void
    {
        $service = $this->getFieldServicesUploadService();
        self::assertNotNull($service);

        $companyIngestion = $this->getStochasticCompanyIngestionService();
        self::assertNotNull($companyIngestion);
        $companyIngestion->updateAllCompaniesFromStochasticRoster();
        $testCompany = $this->companyRepository->findOneByIdentifier('SM000250');
        self::assertNotNull($testCompany);
        $trade = $this->tradeRepository->getTrade(
            Trade::hvac()
        );
        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );

        // Create a mock import ID
        $importId = 4;

        $service->processJobsOrInvoiceFile(
            __DIR__.'/../Files/SM000250-service-titan-jobs-small.csv',
            $testCompany,
            $trade,
            $software,
            101
        );

        $savedJobs = $this->ingestRepository->getInvoiceRecordsForTenant($testCompany->getIntacctId());
        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $testCompany->getIntacctId(),
            'invoices_stream'
        );
        self::assertCount(14, $savedJobs);
        // og: 152767
        // - 152752
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CompanyProcessDispatchException
     * @throws CouldNotReadSheet
     * @throws DecodingExceptionInterface
     * @throws Exception
     * @throws FieldsAreMissing
     * @throws IOException
     * @throws NoFilePathWasProvided
     * @throws ReaderNotOpenedException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \DateMalformedStringException
     * @throws \League\Csv\Exception
     */
    public function testProcessCsvMembersFile(): void
    {
        $service = $this->getFieldServicesUploadService();
        self::assertNotNull($service);

        $companyIngestion = $this->getStochasticCompanyIngestionService();
        self::assertNotNull($companyIngestion);
        $companyIngestion->updateAllCompaniesFromStochasticRoster();
        $testCompany = $this->companyRepository->findOneByIdentifier('SM000250');
        self::assertNotNull($testCompany);

        $trade = $this->tradeRepository->getTrade(
            Trade::hvac()
        );

        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );

        // Create a mock import ID
        $importId = 5;

        $service->processMembersFile(
            __DIR__.'/../Files/SM000250-service-titan-members.csv',
            $testCompany,
            true,
            $trade,
            $software,
            101
        );

        $savedMembers = $this->ingestRepository->getMemberRecordsForTenant($testCompany->getIntacctId());
        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $testCompany->getIntacctId(),
            'members_stream'
        );
        self::assertCount(4430, $savedMembers);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CompanyProcessDispatchException
     * @throws CouldNotReadSheet
     * @throws DecodingExceptionInterface
     * @throws Exception
     * @throws FieldsAreMissing
     * @throws IOException
     * @throws NoFilePathWasProvided
     * @throws ReaderNotOpenedException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \DateMalformedStringException
     * @throws \League\Csv\Exception
     */
    public function testProcessExcelMembersFile(): void
    {
        $service = $this->getFieldServicesUploadService();
        self::assertNotNull($service);

        $companyIngestion = $this->getStochasticCompanyIngestionService();
        self::assertNotNull($companyIngestion);
        $companyIngestion->updateAllCompaniesFromStochasticRoster();
        $testCompany = $this->companyRepository->findOneByIdentifier('SM000250');
        self::assertNotNull($testCompany);

        $trade = $this->tradeRepository->getTrade(
            Trade::hvac()
        );

        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );

        // Create a mock import ID
        $importId = 6;

        $service->processMembersFile(
            __DIR__.'/../Files/SM000250-service-titan-members.xlsx',
            $testCompany,
            true,
            $trade,
            $software,
            101
        );

        $savedMembers = $this->ingestRepository->getMemberRecordsForTenant($testCompany->getIntacctId());
        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $testCompany->getIntacctId(),
            'members_stream'
        );
        self::assertCount(4430, $savedMembers);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CompanyProcessDispatchException
     * @throws CouldNotReadSheet
     * @throws DecodingExceptionInterface
     * @throws Exception
     * @throws FieldsAreMissing
     * @throws IOException
     * @throws NoFilePathWasProvided
     * @throws ReaderNotOpenedException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \DateMalformedStringException
     * @throws \League\Csv\Exception
     */
    public function testProcessCsvJobsOrInvoiceFileWithJobTypeInvoiceSummaryZone(): void
    {
        $service = $this->getFieldServicesUploadService();
        self::assertNotNull($service);

        $companyIngestion = $this->getStochasticCompanyIngestionService();
        self::assertNotNull($companyIngestion);
        $companyIngestion->updateAllCompaniesFromStochasticRoster();
        $testCompany = $this->companyRepository->findOneByIdentifier('SM000250');
        self::assertNotNull($testCompany);
        $trade = $this->tradeRepository->getTrade(
            Trade::hvac()
        );
        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );

        // Create a mock import ID
        $importId = 7;

        $service->processJobsOrInvoiceFile(
            __DIR__.'/../Files/SM000250-service-titan-jobs-additional-fields.csv',
            $testCompany,
            $trade,
            $software,
            101
        );

        $savedJobs = $this->ingestRepository->getInvoiceRecordsForTenant($testCompany->getIntacctId());
        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $testCompany->getIntacctId(),
            'invoices_stream'
        );
        self::assertGreaterThan(10, count($savedJobs));
        self::assertCount(14, $savedJobs);
    }
}
