<?php

namespace App\Module\Stochastic\Feature\PostageUploads\Service;

use App\Entity\BatchPostage;
use App\Entity\Company;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\FileDoesNotExist;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Exception\UnsupportedImportTypeException;
use App\Module\Stochastic\Feature\PostageUploads\DTO\UploadPostageExpenseDTO;
use App\Module\Stochastic\Feature\PostageUploads\Repository\BatchPostageRepository;
use App\Module\Stochastic\Feature\PostageUploads\ValueObject\BatchPostageRecord;
use App\Module\Stochastic\Feature\PostageUploads\ValueObject\BatchPostageRecordMap;
use App\Service\AbstractUploadService;
use App\ValueObject\NumericValue;
use App\ValueObject\TabularFile;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class UploadPostageExpenseService extends AbstractUploadService
{
    public function __construct(
        private BatchPostageRepository $batchPostageRepository,
        LoggerInterface $logger,
        string $tempDirectory,
    ) {
        parent::__construct($tempDirectory, $logger);
    }

    /**
     * @throws FieldsAreMissing
     * @throws UnsupportedImportTypeException
     * @throws UnsupportedFileTypeException
     * @throws Exception
     * @throws FileDoesNotExist
     * @throws NoFilePathWasProvided
     * @throws IOException
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     */
    public function handleFromUpload(
        UploadPostageExpenseDTO $dto,
        Company $company,
        UploadedFile $uploadedFile,
    ): void {
        if (UploadPostageExpenseDTO::VENDOR_USPS !== $dto->vendor) {
            throw new UnsupportedImportTypeException($dto->vendor);
        }
        if (UploadPostageExpenseDTO::TYPE_EXPENSE !== $dto->type) {
            throw new UnsupportedImportTypeException($dto->type);
        }

        $uploadedFilePath = $this->moveUploadedFile(
            $uploadedFile,
            $company,
            'postage-expense'
        );

        $this->handleWithDirectFilePath(
            $uploadedFilePath,
        );
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
    public function handleWithDirectFilePath(
        string $uploadedFilePath
    ): void {
        $tabularData = TabularFile::fromExcelOrCsvFile(
            new BatchPostageRecordMap(),
            $uploadedFilePath,
            TabularFile::EXCEL_PHPOFFICE
        );
        $allRows = $tabularData->getRowIteratorForColumns($tabularData->getHeadersAsArray());

        foreach ($allRows as $row) {
            /** @var BatchPostageRecord $batchPostageRecord */
            $batchPostageRecord = BatchPostageRecord::fromTabularRecord($row);
            $batchPostageRecord->cost =
                NumericValue::fromMixedInput($batchPostageRecord->cost)->toSanitizedString();

            $reference = $batchPostageRecord->reference;
            $quantitySent = (int) $batchPostageRecord->quantity_sent;
            $cost = (float) $batchPostageRecord->cost;

            if ($quantitySent < 0) {
                $quantitySent = 0;
            }

            if ($cost < 0) {
                $cost *= -1;
            }

            $batchPostage = $this->batchPostageRepository->findOneByReference(
                $reference,
            ) ?? new BatchPostage();

            $batchPostage->setReference($reference);
            $batchPostage->setQuantitySent($quantitySent);
            $batchPostage->setCost((string)$cost);

            $this->batchPostageRepository->persistBatchPostage($batchPostage);
        }

        $this->batchPostageRepository->flushEntityManager();
    }
}
