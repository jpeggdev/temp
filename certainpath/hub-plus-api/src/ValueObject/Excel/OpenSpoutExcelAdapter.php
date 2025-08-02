<?php

namespace App\ValueObject\Excel;

use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Reader\XLSX\Reader as OpenSpoutReader;
use OpenSpout\Reader\XLSX\SheetIterator;

readonly class OpenSpoutExcelAdapter implements ExcelAdapter
{
    public function __construct(
        private string $sheetFilePath,
    ) {
    }

    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function getRowIterator(array $columns): \Generator
    {
        $reader = new OpenSpoutReader();
        $reader->open($this->sheetFilePath);

        $headerRow = [];
        try {
            /** @var \Iterator|SheetIterator $sheetIterator */
            $sheetIterator = $reader->getSheetIterator();
            foreach ($sheetIterator as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    if (1 === $rowIndex) {
                        foreach ($row->getCells() as $cell) {
                            $headerRow[] = $cell->getValue();
                        }
                        continue; // Skip the header row
                    }

                    $completeRow = array_fill_keys($columns, '');
                    foreach ($row->getCells() as $key => $cell) {
                        if (isset($headerRow[$key]) && in_array($headerRow[$key], $columns, true)) {
                            $completeRow[$headerRow[$key]] = $cell->getValue();
                        }
                    }
                    yield $completeRow;
                }
                break; // Only read the first sheet
            }
        } finally {
            $reader->close();
        }
    }

    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function getHeaders(): array
    {
        $reader = new OpenSpoutReader();
        $reader->open($this->sheetFilePath);

        $headers = [];
        try {
            /** @var \Iterator|SheetIterator $sheetIterator */
            $sheetIterator = $reader->getSheetIterator();
            foreach ($sheetIterator as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    if (1 === $rowIndex) {
                        foreach ($row->getCells() as $cell) {
                            $headers[] = $cell->getValue();
                        }
                        break; // Only get the first row (headers)
                    }
                }
                break; // Only read the first sheet
            }
        } finally {
            $reader->close();
        }

        return $headers;
    }
}
