<?php

namespace App\ValueObject\Excel;

use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use avadim\FastExcelReader\Excel;
use avadim\FastExcelReader\Sheet;

class FastExcelReaderAdapter implements ExcelAdapter
{
    private ?Sheet $sheet = null;

    public function __construct(
        private readonly string $sheetFilePath,
    ) {
    }

    /**
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     */
    public function getRowIterator(array $columns): \Generator
    {
        $sheet = $this->getSheet();
        if (!$sheet) {
            throw new CouldNotReadSheet($this->sheetFilePath);
        }

        foreach ($sheet->nextRow(true) as $rowNumber => $row) {
            if (1 === $rowNumber) {
                continue; // Skip the header row
            }
            $completeRow = array_fill_keys($columns, '');
            foreach ($row as $key => $value) {
                if (array_key_exists($key, $completeRow)) {
                    $completeRow[$key] = $value;
                }
            }
            yield $completeRow;
        }
    }

    /**
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     */
    public function getHeaders(): array
    {
        $sheet = $this->getSheet();
        if (!$sheet) {
            throw new CouldNotReadSheet($this->sheetFilePath);
        }
        $headers = [];
        foreach ($sheet->nextRow(false, Excel::KEYS_ZERO_BASED) as $rowIndex => $row) {
            if (0 === $rowIndex) {
                $headers = array_values($row);
                break; // Only get the first row (headers)
            }
        }

        return $headers;
    }

    /**
     * @throws ExcelFileIsCorrupted
     */
    private function getSheet(): ?Sheet
    {
        if ($this->sheet) {
            return $this->sheet;
        }
        try {
            $this->sheet = Excel::open($this->sheetFilePath)->sheet();

            return $this->sheet;
        } catch (\Throwable $error) {
            throw new ExcelFileIsCorrupted(
                $this->sheetFilePath
                .
                ' / '
                .$error->getMessage()
            );
        }
    }
}
