<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\FieldServiceSoftware;
use App\Entity\Trade;
use App\Exception\CompanyProcessDispatchException;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Repository\CompanyDataImportJobRepository;
use App\Repository\External\IngestRepository;
use App\ValueObject\ByteSize;
use App\ValueObject\InvoiceRecord;
use App\ValueObject\InvoiceRecordMap;
use App\ValueObject\MemberRecord;
use App\ValueObject\MemberRecordMap;
use App\ValueObject\TabularFile;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FieldServicesUploadService
{
    private const int INSERT_RECORD_BATCH_SIZE = 2000;

    public function __construct(
        private readonly IngestRepository $ingestRepository,
        private readonly UnificationCompanyProcessingDispatchService $jobDispatch,
        private readonly LoggerInterface $logger,
        private readonly CompanyDataImportJobRepository $companyDataImportJobRepository,
    ) {
    }

    /**
     * @throws IOException
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws Exception
     * @throws NoFilePathWasProvided
     */
    public function preProcessJobsOrInvoiceFile(
        string $jobsOrInvoiceFilePath,
        Company $company,
        Trade $trade,
        FieldServiceSoftware $software,
    ): void {
        $this->logger->info('Pre-Process: Invoice Upload: Processing file', [
            'file' => $jobsOrInvoiceFilePath,
            'company' => $company->getIntacctId(),
            'trade' => $trade->getLongName(),
            'software' => $software->getName(),
        ]);
        $tabularData = TabularFile::fromExcelOrCsvFile(new InvoiceRecordMap(), $jobsOrInvoiceFilePath);
        $allRows = $tabularData->getRowIteratorForColumns($tabularData->getHeadersAsArray());
        foreach ($allRows as $row) {
            $row['tenant'] = $company->getIntacctId();
            $row['trade'] = $trade->getLongName();
            $row['software'] = $software->getName();
            $row['hub_plus_import_id'] = null;
            InvoiceRecord::fromTabularRecord($row);
            break;
        }
    }

    /**
     * @throws IOException
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws Exception
     * @throws NoFilePathWasProvided
     */
    public function preProcessMembersFile(
        string $membersFilePath,
        Company $company,
        bool $isActiveMembersFile,
        Trade $trade,
        FieldServiceSoftware $software,
    ): void {
        $this->logger->info('Pre-Process: Customer Upload: Processing file', [
            'file' => $membersFilePath,
            'company' => $company->getIntacctId(),
            'software' => $software->getName(),
            'trade' => $trade->getLongName(),
            'activeFile?' => $isActiveMembersFile ? 'Yes' : 'No',
        ]);
        $tabularData = TabularFile::fromExcelOrCsvFile(new MemberRecordMap(), $membersFilePath);
        $allRows = $tabularData->getRowIteratorForColumns($tabularData->getHeadersAsArray());
        $version = time();
        foreach ($allRows as $row) {
            $row['tenant'] = $company->getIntacctId();
            $row['trade'] = $trade->getLongName();
            $row['software'] = $software->getName();
            $row['hub_plus_import_id'] = null;
            $row['version'] = $version;
            MemberRecord::fromTabularRecord($row);
            break;
        }
    }

    /**
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws CompanyProcessDispatchException
     * @throws ServerExceptionInterface
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws NoFilePathWasProvided
     * @throws IOException
     * @throws RedirectionExceptionInterface
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws TransportExceptionInterface
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws \JsonException
     */
    public function processJobsOrInvoiceFile(
        string $jobsOrInvoiceFilePath,
        Company $company,
        Trade $trade,
        FieldServiceSoftware $software,
        ?int $importId = null,
    ): int {
        $this->logger->info('Invoice Upload: Processing file', [
            'file' => $jobsOrInvoiceFilePath,
            'company' => $company->getIntacctId(),
            'trade' => $trade->getLongName(),
            'software' => $software->getName(),
        ]);

        $tabularData = TabularFile::fromExcelOrCsvFile(new InvoiceRecordMap(), $jobsOrInvoiceFilePath);
        $fileSize = $tabularData->getFileSize();

        $recordCounter = 0;
        $rowByteCount = 0;
        $recordsToAdd = [];

        foreach ($tabularData->getRowIteratorForColumns($tabularData->getHeadersAsArray()) as $row) {
            $row['tenant'] = $company->getIntacctId();
            $row['trade'] = $trade->getLongName();
            $row['software'] = $software->getName();
            $row['hub_plus_import_id'] = $importId;

            $rowByteSize = ByteSize::fromArray($row)->getAdjustedValue();
            $rowByteCount += $rowByteSize;
            /** @var InvoiceRecord $invoiceRecord */
            $invoiceRecord = InvoiceRecord::fromTabularRecord($row);

            try {
                $invoiceRecord->processCustomerNames();
                $invoiceRecord->validateFieldValues();
            } catch (\Throwable $e) {
                $this->logger->warning('Skipping invoice record', ['error' => $e->getMessage()]);
                continue;
            }

            $recordsToAdd[] = $invoiceRecord;
            ++$recordCounter;

            if (0 === $recordCounter % self::INSERT_RECORD_BATCH_SIZE) {
                $this->ingestRepository->insertInvoiceRecords($recordsToAdd);
                $recordsToAdd = [];

                $fraction = $rowByteCount / $fileSize;
                $progress = "Uploaded {$recordCounter} invoice records so far";
                $this->companyDataImportJobRepository->updateProgressPercent(
                    $importId,
                    $progress,
                    $fraction * 50
                );
            }
        }

        $this->companyDataImportJobRepository->updateRowCount($importId, $recordCounter);

        if (count($recordsToAdd) > 0) {
            $this->ingestRepository->insertInvoiceRecords($recordsToAdd);
        }

        if ($recordCounter > 0) {
            $finalText = "Processed {$recordCounter} invoice records in total";
            $this->companyDataImportJobRepository->updateProgressPercent($importId, $finalText, 50, 'PROCESSING', true);
            $this->jobDispatch->dispatchProcessingForCompany($company);
        } else {
            $finalText = 'No invoice records to process';
            $this->companyDataImportJobRepository->updateProgressPercent($importId, $finalText, 100, 'COMPLETED');
        }

        return $recordCounter;
    }

    /**
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws CompanyProcessDispatchException
     * @throws ServerExceptionInterface
     * @throws Exception
     * @throws NoFilePathWasProvided
     * @throws IOException
     * @throws RedirectionExceptionInterface
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws TransportExceptionInterface
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws \JsonException
     * @throws \Doctrine\DBAL\Exception
     */
    public function processMembersFile(
        string $membersFilePath,
        Company $company,
        bool $isActiveMembersFile,
        Trade $trade,
        FieldServiceSoftware $software,
        ?int $importId = null,
    ): int {
        $this->logger->info('Customer Upload: Processing file', [
            'file' => $membersFilePath,
            'company' => $company->getIntacctId(),
            'software' => $software->getName(),
            'trade' => $trade->getLongName(),
            'activeFile?' => $isActiveMembersFile ? 'Yes' : 'No',
        ]);

        $tabularData = TabularFile::fromExcelOrCsvFile(new MemberRecordMap(), $membersFilePath);
        $fileSize = $tabularData->getFileSize();

        $recordCounter = 0;
        $rowByteCount = 0;
        $recordsToAdd = [];
        $version = time();

        foreach ($tabularData->getRowIteratorForColumns($tabularData->getHeadersAsArray()) as $row) {
            $row['tenant'] = $company->getIntacctId();
            $row['trade'] = $trade->getLongName();
            $row['software'] = $software->getName();
            $row['version'] = $version;
            $row['hub_plus_import_id'] = $importId;

            $rowByteSize = ByteSize::fromArray($row)->getAdjustedValue();
            $rowByteCount += $rowByteSize;

            /** @var MemberRecord $memberRecord */
            $memberRecord = MemberRecord::fromTabularRecord($row);

            try {
                $memberRecord->processCustomerNames();
                $memberRecord->processMembershipType();
                $memberRecord->validateFieldValues();
            } catch (\Throwable $e) {
                $this->logger->warning('Skipping member record', ['error' => $e->getMessage()]);
                continue;
            }

            $recordsToAdd[] = $memberRecord;
            ++$recordCounter;

            if (0 === $recordCounter % self::INSERT_RECORD_BATCH_SIZE) {
                $this->insertMemberRecordBatch($recordsToAdd);
                $recordsToAdd = [];

                $fraction = $rowByteCount / $fileSize;
                $progress = "Uploaded {$recordCounter} customer records so far";
                $this->companyDataImportJobRepository->updateProgressPercent(
                    $importId,
                    $progress,
                    $fraction * 50
                );
            }
        }

        $this->companyDataImportJobRepository->updateRowCount(
            $importId,
            $recordCounter
        );

        if (count($recordsToAdd) > 0) {
            $this->insertMemberRecordBatch($recordsToAdd);
        }

        if ($recordCounter > 0) {
            $finalText = "Uploaded {$recordCounter} customer records in total";
            $this->companyDataImportJobRepository->updateProgressPercent(
                $importId,
                $finalText,
                50,
                'PROCESSING',
                true
            );
            $this->jobDispatch->dispatchProcessingForCompany(
                $company
            );
        } else {
            $finalText = 'No customer records to process';
            $this->companyDataImportJobRepository->updateProgressPercent(
                $importId,
                $finalText,
                100,
                'COMPLETED'
            );
        }

        return $recordCounter;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function insertMemberRecordBatch(array $recordsToAdd): void
    {
        $this->ingestRepository->insertMemberRecords($recordsToAdd);
    }
}
