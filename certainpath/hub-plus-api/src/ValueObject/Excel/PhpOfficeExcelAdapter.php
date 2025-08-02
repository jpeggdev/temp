<?php

namespace App\ValueObject\Excel;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PhpOfficeExcelAdapter implements ExcelAdapter
{
    private ?Worksheet $sheetPhpOffice = null;

    public function __construct(
        private readonly string $sheetFilePath,
    ) {
    }

    public function getRowIterator(array $columns): \Generator
    {
        $sheet = $this->getSheetPhpOffice();

        $headerRow = [];
        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue() ?? '';
            }

            if (1 === $rowIndex) {
                $headerRow = $rowData;
                continue; // Skip the header row
            }

            $completeRow = array_fill_keys($columns, '');
            foreach ($rowData as $key => $value) {
                if (isset($headerRow[$key]) && in_array($headerRow[$key], $columns, true)) {
                    $completeRow[$headerRow[$key]] = $value;
                }
            }
            yield $completeRow;
        }
    }

    public function getHeaders(): array
    {
        $sheet = $this->getSheetPhpOffice();

        $headers = [];
        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            if (1 === $rowIndex) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                foreach ($cellIterator as $cell) {
                    $headers[] = $cell->getValueString();
                }
                break; // Only get the first row (headers)
            }
        }

        return $headers;
    }

    private function getSheetPhpOffice(): Worksheet
    {
        if ($this->sheetPhpOffice) {
            return $this->sheetPhpOffice;
        }
        $reader = IOFactory::createReaderForFile($this->sheetFilePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($this->sheetFilePath);
        $sheet = $spreadsheet->getActiveSheet();
        $this->sheetPhpOffice = $sheet;

        return $sheet;
    }
}
