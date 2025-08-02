<?php

namespace App\Parsers;

use PhpOffice\PhpSpreadsheet\IOFactory as FileReader;
use InvalidArgumentException;

use function App\Functions\app_getSanitizedHeaderValue;
use function App\Functions\app_nullify;

class RecordParser
{
    protected array $headers = [ ];
    protected array $records = [ ];
    protected string $filePath;

    public function __construct(string $filePath = null)
    {
        $this->filePath = $filePath;
    }

    public function parseRecords(int $limit = null): self
    {
        $this->headers = [];
        $this->records = [];

        if (is_readable($this->filePath)) {
            $fileType = FileReader::identify($this->filePath);

            $reader = FileReader::createReaderForFile($this->filePath);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($this->filePath);

            $sheet = $spreadsheet->getActiveSheet();

            if (!is_object($sheet)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "The file '%s' does not appear to be a valid CSV or Excel file.",
                        $this->filePath
                    )
                );
            }

            $rowIterator = $sheet->getRowIterator();
            $rowCount = 0;

            foreach ($rowIterator as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                if ($rowCount === 0) {
                    $this->headers = array_values(array_filter(array_map(static function ($cell) {
                        return $cell->getValue();
                    }, iterator_to_array($cellIterator))));

                    if (!is_array($this->headers) || 0 === count($this->headers)) {
                        throw new InvalidArgumentException(
                            sprintf(
                                "The first row of '%s' does not appear to contain header columns.",
                                $this->filePath
                            )
                        );
                    }

                    $this->sanitizeHeaders();
                } else {
                    $cells = array_map(static function ($cell) {
                        return $cell->getValue();
                    }, iterator_to_array($cellIterator));

                    $record = array_map(static function (string $value = null) {
                        return app_nullify($value);
                    }, $cells);

                    $record = array_pad($record, count($this->headers), null);

                    $this->records[] = array_combine($this->headers, $record);

                    if ($limit !== null && count($this->records) >= $limit) {
                        break;
                    }
                }

                $rowCount++;
            }

            unset($reader);
        }

        return $this;
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return void
     */
    protected function sanitizeHeaders(): void
    {
        array_walk($this->headers, static function (string &$column = null, int $idx) {
            $column = app_getSanitizedHeaderValue($column, $idx);
        });
    }
}
