<?php

namespace App\Tests\Service;

use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Tests\AbstractKernelTestCase;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class UploadPostageExpenseServiceTest extends AbstractKernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testReportIngestExcel(): void
    {
        $this->assertFileProcessingIsCorrect(
            __DIR__.'/../Files/usps/report-2025.07.21.xls',
            837
        );
    }

    /**
     * @throws \Exception
     */
    public function testReportIngestCsv(): void
    {
        $this->assertFileProcessingIsCorrect(
            __DIR__.'/../Files/usps/report-2025.07.21.csv',
            837
        );
    }

    /**
     * @param string $fileToTest
     * @param int $expectedRecordCount
     * @return void
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     * @throws FieldsAreMissing
     * @throws NoFilePathWasProvided
     * @throws UnsupportedFileTypeException
     * @throws Exception
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    private function assertFileProcessingIsCorrect(string $fileToTest, int $expectedRecordCount): void
    {
        self::assertFileExists($fileToTest);

        $service = $this->getUploadPostageExpenseService();
        self::assertNotNull($service);

        $service->handleWithDirectFilePath(
            $fileToTest,
        );

        $allPostageRecords = $this->batchPostageRepository->all();

        self::assertCount($expectedRecordCount, $allPostageRecords);

        foreach ($allPostageRecords as $postageRecord) {
            self::assertGreaterThan(0, (float)$postageRecord->getCost());
            if ($postageRecord->getReference() !== 'N/A') {
                self::assertGreaterThan(
                    0,
                    $postageRecord->getQuantitySent(),
                    $postageRecord->getReference()
                );
            }
        }
    }
}
