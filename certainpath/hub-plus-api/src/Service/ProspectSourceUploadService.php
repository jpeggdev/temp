<?php

namespace App\Service;

use App\Entity\CompanyDataImportJob;
use App\Exception\CompanyProcessDispatchException;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Repository\CompanyDataImportJobRepository;
use App\Repository\External\IngestRepository;
use App\ValueObject\ByteSize;
use App\ValueObject\ProspectRecord;
use App\ValueObject\ProspectRecordMap;
use App\ValueObject\TabularFile;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class ProspectSourceUploadService
{
    private const int INSERT_RECORD_BATCH_SIZE = 2000;

    public function __construct(
        private IngestRepository $ingestRepository,
        private UnificationCompanyProcessingDispatchService $jobDispatch,
        private CompanyDataImportJobRepository $companyDataImportJobRepository,
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
    public function preProcessProspectsFile(
        CompanyDataImportJob $companyDataImportJob,
    ): void {
        $tabularData = TabularFile::fromExcelOrCsvFile(
            new ProspectRecordMap(),
            $companyDataImportJob->getFilePath()
        );
        $allRows = $tabularData->getRowIteratorForColumns($tabularData->getHeadersAsArray());
        $version = time();
        foreach ($allRows as $row) {
            $row['tenant'] = $companyDataImportJob->getIntacctId();
            $row['software'] = $companyDataImportJob->getSoftware();
            $row['version'] = $version;
            $row['tag'] = $companyDataImportJob->getTag();
            $row['country'] = 'US';
            $row['hub_plus_import_id'] = $companyDataImportJob->getId();
            ProspectRecord::fromTabularRecord($row);
            break;
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CompanyProcessDispatchException
     * @throws CouldNotReadSheet
     * @throws DecodingExceptionInterface
     * @throws ExcelFileIsCorrupted
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
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     */
    public function processProspectsFile(
        CompanyDataImportJob $companyDataImportJob,
    ): void {
        $tabularData = TabularFile::fromExcelOrCsvFile(
            new ProspectRecordMap(),
            $companyDataImportJob->getFilePath()
        );

        $fileSize = $tabularData->getFileSize();

        $recordCounter = 0;
        $rowByteCount = 0;
        $recordsToAdd = [];
        $version = time();

        foreach ($tabularData->getRowIteratorForColumns($tabularData->getHeadersAsArray()) as $row) {
            $row['tenant'] = $companyDataImportJob->getIntacctId();
            $row['software'] = $companyDataImportJob->getSoftware();
            $row['version'] = $version;
            $row['tag'] = $companyDataImportJob->getTag();
            $row['country'] = 'US';
            $row['hub_plus_import_id'] = $companyDataImportJob->getId();

            $rowByteSize = ByteSize::fromArray($row)->getAdjustedValue();
            $rowByteCount += $rowByteSize;

            /** @var ProspectRecord $prospectRecord */
            $prospectRecord = ProspectRecord::fromTabularRecord($row);
            $recordsToAdd[] = $prospectRecord;
            ++$recordCounter;

            $this->companyDataImportJobRepository->updateRowCount(
                $companyDataImportJob->getId(),
                $recordCounter
            );

            if (0 === $recordCounter % self::INSERT_RECORD_BATCH_SIZE) {
                $this->ingestRepository->insertProspectRecords($recordsToAdd);
                $recordsToAdd = [];

                $fraction = $rowByteCount / $fileSize;
                $progressText = "Uploaded {$recordCounter} prospect records so far";
                $this->companyDataImportJobRepository->updateProgressPercent(
                    $companyDataImportJob->getId(),
                    $progressText,
                    $fraction * 50
                );
            }
        }

        $this->companyDataImportJobRepository->updateRowCount(
            $companyDataImportJob->getId(),
            $recordCounter
        );

        if (count($recordsToAdd) > 0) {
            $this->ingestRepository->insertProspectRecords($recordsToAdd);
        }

        if ($recordCounter > 0) {
            $finalText = "Processed {$recordCounter} prospect records in total";
            $this->companyDataImportJobRepository->updateProgressPercent(
                $companyDataImportJob->getId(),
                $finalText,
                50,
                'PROCESSING',
                true
            );

            $this->jobDispatch->dispatchProcessingForCompany(
                $companyDataImportJob->getCompany()
            );
        } else {
            $finalText = 'No customer records to process';
            $this->companyDataImportJobRepository->updateProgressPercent(
                $companyDataImportJob->getId(),
                $finalText,
                100,
                'COMPLETED'
            );
        }
    }
}
