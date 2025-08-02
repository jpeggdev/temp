<?php

namespace App\ValueObject;

use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\ValueObject\Excel\ExcelAdapter;
use App\ValueObject\Excel\FastExcelReaderAdapter;
use App\ValueObject\Excel\OpenSpoutExcelAdapter;
use App\ValueObject\Excel\PhpOfficeExcelAdapter;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class TabularFile
{
    public const string EXCEL_FAST = 'excel-fast';
    public const string EXCEL_OPENSPOUT = 'excel-openspout';
    public const string EXCEL_PHPOFFICE = 'excel-phpoffice';

    /** @var AbstractRecordMap[] */
    private array $maps;

    private ExcelAdapter $selectedExcelAdapter;

    /**
     * @throws NoFilePathWasProvided
     */
    public function __construct(
        AbstractRecordMap $recordMap,
        private readonly string $excelOrCsvFilePath,
        ?string $providerName,
    ) {
        if (empty($this->excelOrCsvFilePath)) {
            throw new NoFilePathWasProvided();
        }
        $this->maps = [
            $recordMap,
        ];
        $adapterMap = [
            self::EXCEL_FAST => FastExcelReaderAdapter::class,
            self::EXCEL_OPENSPOUT => OpenSpoutExcelAdapter::class,
            self::EXCEL_PHPOFFICE => PhpOfficeExcelAdapter::class,
        ];
        if (!$providerName || !isset($adapterMap[$providerName])) {
            $providerName = self::EXCEL_FAST;
        }
        $this->selectedExcelAdapter =
            new $adapterMap[$providerName]($this->excelOrCsvFilePath);
    }

    /**
     * @throws NoFilePathWasProvided
     */
    public static function fromExcelOrCsvFile(
        AbstractRecordMap $recordMap,
        string $excelOfCsvFilePath,
        ?string $providerName = null,
    ): self {
        return new self(
            $recordMap,
            $excelOfCsvFilePath,
            $providerName
        );
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
     */
    public function getHeadersAsArray(): array
    {
        if ($this->isExcel()) {
            return $this->getHeadersFromExcel();
        }
        if ($this->isCsv()) {
            return $this->getHeadersFromCsv();
        }
        throw new UnsupportedFileTypeException($this->excelOrCsvFilePath);
    }

    /**
     * @throws Exception
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws UnsupportedFileTypeException
     */
    public function getRowIteratorForColumns(array $columns): \Generator
    {
        if ($this->isExcel()) {
            return $this->getRowIteratorFromExcel($columns);
        }
        if ($this->isCsv()) {
            return $this->getRowIteratorFromCsv($columns);
        }
        throw new UnsupportedFileTypeException($this->excelOrCsvFilePath);
    }

    private function getRowIteratorFromExcel(array $columns): \Generator
    {
        return $this->selectedExcelAdapter->getRowIterator($columns);
    }

    /**
     * @throws UnavailableStream
     * @throws SyntaxError
     * @throws Exception
     */
    private function getRowIteratorFromCsv(array $columns): \Generator
    {
        $csv = Reader::createFromPath($this->excelOrCsvFilePath, 'r');
        $csv->setHeaderOffset(0); // Set the header offset to the first row

        foreach ($csv->getRecords() as $record) {
            $filteredRow = [];
            foreach ($columns as $column) {
                $filteredRow[$column] = $record[$column];
            }
            yield $filteredRow;
        }
    }

    /**
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    private function getHeadersFromExcel(): array
    {
        $headers = $this->selectedExcelAdapter->getHeaders();

        return $this->getTargetHeaders($headers);
    }

    /**
     * @throws UnavailableStream
     * @throws SyntaxError
     * @throws Exception
     */
    private function getHeadersFromCsv(): array
    {
        $csv = Reader::createFromPath($this->excelOrCsvFilePath, 'r');
        $csv->setHeaderOffset(0); // Set the header offset to the first row

        return $csv->getHeader();
    }

    public function isExcel(): bool
    {
        return
            'xlsx' === pathinfo($this->excelOrCsvFilePath, PATHINFO_EXTENSION)
            || 'xls' === pathinfo($this->excelOrCsvFilePath, PATHINFO_EXTENSION)
            || 'xlsm' === pathinfo($this->excelOrCsvFilePath, PATHINFO_EXTENSION)
        ;
    }

    public function isCsv(): bool
    {
        return
            'csv' === pathinfo($this->excelOrCsvFilePath, PATHINFO_EXTENSION)
            ||
            'txt' === pathinfo($this->excelOrCsvFilePath, PATHINFO_EXTENSION)
        ;
    }

    private function getTargetHeaders(array $headers): array
    {
        $targetHeaders = [];
        foreach ($headers as $header) {
            /** @var AbstractRecordMap $map */
            foreach ($this->maps as $map) {
                if ($map->getProperty($header)) {
                    $targetHeaders[] = $header;
                    break;
                }
            }
        }

        return $targetHeaders;
    }

    public function getFileSize(): false|int
    {
        return filesize($this->excelOrCsvFilePath);
    }
}
