<?php

namespace App\Tests\ValueObject;

use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\InvoiceRecordMap;
use App\ValueObject\MemberRecordMap;
use App\ValueObject\TabularFile;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class TabularFileTest extends AbstractKernelTestCase
{
    /**
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     * @throws Exception
     * @throws IOException
     * @throws NoFilePathWasProvided
     * @throws ReaderNotOpenedException
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     */
    public function testCorruptedFileAgainstExcelLibraries(): void
    {
        $brokenExcelFile = __DIR__.'/../Files/broken-customer-list-throws-sheet-related-error.xlsx';
        self::assertFileExists($brokenExcelFile);
        $tabularFileOne = TabularFile::fromExcelOrCsvFile(
            new MemberRecordMap(),
            $brokenExcelFile,
            TabularFile::EXCEL_OPENSPOUT
        );
        $headersOne = $tabularFileOne->getHeadersAsArray();

        $workingExcelFile = __DIR__.'/../Files/copy-of-broken-customer-list-throws-sheet-related-error.xlsx';
        self::assertFileExists($workingExcelFile);
        $tabularFileTwo = TabularFile::fromExcelOrCsvFile(
            new MemberRecordMap(),
            $workingExcelFile,
        );
        $headersTwo = $tabularFileTwo->getHeadersAsArray();

        self::assertSame(
            $headersOne,
            $headersTwo
        );

        $tabularFileThree = TabularFile::fromExcelOrCsvFile(
            new MemberRecordMap(),
            $brokenExcelFile,
            TabularFile::EXCEL_PHPOFFICE
        );
        $headersThree = $tabularFileThree->getHeadersAsArray();

        self::assertSame(
            $headersOne,
            $headersThree
        );

        $secondBrokenExcelFile = __DIR__.'/../Files/possibly-broken-invoice-report.xlsx';
        self::assertFileExists($secondBrokenExcelFile);
        $tabularFileFour = TabularFile::fromExcelOrCsvFile(
            new InvoiceRecordMap(),
            $secondBrokenExcelFile,
        );
        $fileFourIsCorrupted = false;
        try {
            $tabularFileFour->getHeadersAsArray();
        } catch (ExcelFileIsCorrupted $e) {
            $fileFourIsCorrupted = true;
        }
        self::assertFalse($fileFourIsCorrupted);

        $copyOfSecondBrokenFile = __DIR__.'/../Files/copy-of-possibly-broken-invoice-report.xlsx';
        self::assertFileExists($copyOfSecondBrokenFile);
        $tabularFileFive = TabularFile::fromExcelOrCsvFile(
            new InvoiceRecordMap(),
            $copyOfSecondBrokenFile,
        );
        $headersFive = $tabularFileFive->getHeadersAsArray();
        $expectedHeadersFive = [
            'Invoice Date',
            'Invoice #',
            'Job Type',
            'Total',
        ];
        self::assertSame(
            $expectedHeadersFive,
            $headersFive
        );
    }

    /**
     * @throws CouldNotReadSheet
     * @throws Exception
     * @throws NoFilePathWasProvided
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \JsonException
     */
    public function testCorruptedExcelFileThrowsFriendlyException(): void
    {
        $brokenExcelFile = __DIR__.'/../Files/broken-customer-list-throws-sheet-related-error.xlsx';
        $copyOfBrokenExcelFile = __DIR__.'/../Files/copy-of-broken-customer-list-throws-sheet-related-error.xlsx';
        $workingExcelFile = __DIR__.'/../Files/works-customer-list-throws-sheet-related-error.xlsx';
        $csvFile = __DIR__.'/../Files/broken-customer-list-throws-sheet-related-error.csv';

        self::assertFileExists($brokenExcelFile);
        self::assertFileExists($workingExcelFile);
        self::assertFileExists($copyOfBrokenExcelFile);
        self::assertFileExists($csvFile);

        $tabularFile = TabularFile::fromExcelOrCsvFile(
            new MemberRecordMap(),
            $brokenExcelFile
        );

        $thrownException = null;
        try {
            $tabularFile->getHeadersAsArray(); // throw
        } catch (ExcelFileIsCorrupted $exception) {
            $thrownException = $exception;
        }

        self::assertNull($thrownException);

        // 1) open the file in excel
        // 2) from the File menu choose "Save As ..."
        // 3) Keep the same excel format, but give it a new name
        // 4) save it and upload the new file name
    }

    /**
     * @throws Exception
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \JsonException
     * @throws \DateMalformedStringException
     * @throws CouldNotReadSheet
     * @throws NoFilePathWasProvided
     * @throws ExcelFileIsCorrupted
     */
    public function testTabularFileProcessingFromExcel(): void
    {
        $jsonFile = __DIR__.'/../Files/SM000250-service-titan-jobs.json';
        self::assertFileExists($jsonFile);
        $excelFile = __DIR__.'/../Files/SM000250-service-titan-jobs.xlsx';
        self::assertFileExists($excelFile);

        $tabularFiles = [
            TabularFile::fromExcelOrCsvFile(
                new InvoiceRecordMap(),
                $excelFile
            ),
            TabularFile::fromExcelOrCsvFile(
                new InvoiceRecordMap(),
                $excelFile,
                TabularFile::EXCEL_OPENSPOUT
            ),
            // TabularFile::fromExcelOrCsvFile(new InvoiceRecordMap(), $excelFile, TabularFile::EXCEL_PHPOFFICE),
            // PHPOFFICE is unbearably slow, but it works, keeping it here for reference
            // this test will take 4:30 minutes to run if you uncomment PHPOFFICE
            // Otherwise it runs in 10 seconds.
        ];

        foreach ($tabularFiles as $tabularFile) {
            self::assertTrue($tabularFile->isExcel());
            self::assertFalse($tabularFile->isCsv());
            $headers = $tabularFile->getHeadersAsArray();
            self::assertContains(
                'Job Number',
                $headers
            );
            $this->assertTabularDataIsCorrect($tabularFile, $jsonFile);
        }
    }

    /**
     * @throws Exception
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \JsonException
     * @throws \DateMalformedStringException
     */
    public function testTabularFileProcessingFromCsv(): void
    {
        $jsonFile = __DIR__.'/../Files/SM000250-service-titan-jobs.json';
        self::assertFileExists($jsonFile);
        $csvFile = __DIR__.'/../Files/SM000250-service-titan-jobs.csv';
        self::assertFileExists($csvFile);

        $tabularFile = TabularFile::fromExcelOrCsvFile(
            new InvoiceRecordMap(),
            $csvFile
        );
        self::assertFalse($tabularFile->isExcel());
        self::assertTrue($tabularFile->isCsv());
        $headers = $tabularFile->getHeadersAsArray();
        self::assertContains(
            'Job Number',
            $headers
        );
        $this->assertTabularDataIsCorrect($tabularFile, $jsonFile);
    }

    /**
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     * @throws Exception
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     * @throws \DateMalformedStringException
     * @throws \JsonException
     */
    private function assertTabularDataIsCorrect(
        TabularFile $tabularFile,
        string $jsonFile,
    ): void {
        $rows = $tabularFile->getRowIteratorForColumns(
            [
                'Job Number',
                'Invoice Number',
                'Customer ID',
                'Customer Name',
                'Customer First Name',
                'Customer Last Name',
                'Customer Phone Number(s)',
                'Street',
                'Unit',
                'City',
                'State',
                'Zip',
                'Country',
                'First Appointment',
                'Summary',
                'Total',
            ]
        );
        $count = 0;
        $jobs = [];
        foreach ($rows as $row) {
            ++$count;
            $row['Customer ID'] = isset($row['Customer ID']) ? (int) $row['Customer ID'] : null;
            $row['First Appointment'] =
                isset($row['First Appointment']) && $row['First Appointment']
                    ? (new \DateTimeImmutable($row['First Appointment']))->format('n/j/y g:i A')
                    : null;
            if (isset($row['Total'])) {
                $row['Total'] = (float) $row['Total'];
                if ($row['Total'] == (int) $row['Total']) {
                    $row['Total'] = (int) $row['Total'];
                }
            } else {
                $row['Total'] = null;
            }

            $jobs[] = $row;
            if ($count > 10) {
                break;
            }
        }
        $jsonContents = file_get_contents($jsonFile);
        $jsonJobs = json_decode($jsonContents, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($jsonJobs, $jobs);
    }
}
