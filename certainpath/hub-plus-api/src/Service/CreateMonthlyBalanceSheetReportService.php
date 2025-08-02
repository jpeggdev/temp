<?php

declare(strict_types=1);

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CreateMonthlyBalanceSheetReportService
{
    public function generateReport(array $rawData): string
    {
        $data = $this->processData($rawData);

        return $this->generateBalanceSheet($data);
    }

    // region processData
    private function processData(array $rawData): array
    {
        if (empty($rawData)) {
            throw new \InvalidArgumentException('No data provided to process.');
        }

        $firstRecord = $rawData[0];

        $data = [
            'companyName' => $firstRecord['tenant'],
            'reportTitle' => 'Balance Sheet',
            'reportDate' => 'As of '.date('F d, Y', strtotime($firstRecord['report_date'])),
        ];

        $sortedMonths = $this->collectAndSortPeriods($rawData);
        $data['periods'] = $this->formatPeriods($sortedMonths);
        $data['sections'] = $this->processSections($rawData, $sortedMonths);

        return $data;
    }
    // endregion

    // region collectAndSortPeriods
    private function collectAndSortPeriods(array $rawData): array
    {
        $allMonths = [];
        foreach ($rawData as $record) {
            $monthlyTotalsEncoded = json_decode($record['monthlytotal'], true);
            foreach ($monthlyTotalsEncoded as $encodedTotal) {
                $monthAmountPair = json_decode($encodedTotal, true);
                foreach ($monthAmountPair as $month => $amount) {
                    $timestamp = $this->parseMonthToTimestamp($month);
                    if (false !== $timestamp) {
                        $allMonths[$month] = $timestamp;
                    }
                }
            }
        }

        asort($allMonths);

        return array_keys($allMonths);
    }
    // endregion

    // region processSections
    private function processSections(array $rawData, array $sortedMonths): array
    {
        $sections = [];

        foreach ($rawData as $record) {
            $categories = json_decode($record['categories'], true);
            $categories[] = $record['account']; // Append the account to the categories

            $monthlyTotalsEncoded = json_decode($record['monthlytotal'], true);
            $monthlyTotals = [];
            foreach ($monthlyTotalsEncoded as $encodedTotal) {
                $monthAmountPair = json_decode($encodedTotal, true);
                $monthlyTotals = array_merge($monthlyTotals, $monthAmountPair);
            }

            $amounts = [];
            foreach ($sortedMonths as $month) {
                $amount = isset($monthlyTotals[$month]) ? floatval($monthlyTotals[$month]) : 0;
                $amounts[] = $amount;
            }

            $item = [
                'name' => $record['account'],
                'values' => $amounts,
                'indent' => count($categories) - 1,
            ];

            $this->addToSections($sections, $categories, $item);
        }

        return $sections;
    }
    // endregion

    // region generateBalanceSheet
    private function generateBalanceSheet(array $data): string
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Balance Sheet');

        $this->setCompanyNameAndTitle($sheet, $data);

        $headers = array_merge(['Description'], $data['periods']);
        $this->setHeaders($sheet, $headers);

        $sheet->freezePane('A5');

        $row = 5;

        $sectionColors = [
            'ASSETS' => 'D9E1F2',
            'LIABILITIES' => 'F2DCDB',
            'EQUITY' => 'E2EFDA',
        ];

        foreach ($data['sections'] as $sectionName => $items) {
            $color = $sectionColors[strtoupper($sectionName)] ?? 'FFFFFF';
            $this->addSection($sheet, $sectionName, $items, $headers, $color, $row);
        }

        $this->applyBorders($sheet, $headers, $row - 1);
        $this->autoFitColumns($sheet, count($headers));

        return $this->saveSpreadsheetToString($spreadsheet);
    }
    // endregion

    // region setCompanyNameAndTitle
    private function setCompanyNameAndTitle(Worksheet $sheet, array $data): void
    {
        $sheet->setCellValue('A1', $data['companyName']);
        $lastColumn = $this->getExcelColumnName(count($data['periods']) + 1);
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', $data['reportTitle'].' '.$data['reportDate']);
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    // endregion

    // region setHeaders
    private function setHeaders(Worksheet $sheet, array $headers): void
    {
        $sheet->fromArray($headers, null, 'A4');
        $lastColumnIndex = count($headers);
        $lastColumn = $this->getExcelColumnName($lastColumnIndex);

        $sheet->getStyle("A4:{$lastColumn}4")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A4:{$lastColumn}4")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
        $sheet->getStyle("A4:{$lastColumn}4")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$lastColumn}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    // endregion

    // region addSection
    private function addSection(
        Worksheet $sheet,
        int|string $sectionName,
        mixed $items,
        array $headers,
        string $fillColor,
        int &$row,
    ): void {
        $this->addSectionTitle($sheet, $sectionName, $fillColor, $row, count($headers));
        $this->addItems($sheet, $items, $row, $headers);
        ++$row;
    }
    // endregion

    // region addSectionTitle
    private function addSectionTitle(
        Worksheet $sheet,
        int|string $sectionName,
        string $fillColor,
        int &$row,
        int $headerCount,
    ): void {
        $lastColumn = $this->getExcelColumnName($headerCount);
        $sheet->setCellValue("A$row", strtoupper($sectionName));
        $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");
        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
            ->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fillColor);
        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        ++$row;
    }
    // endregion

    // region addItems
    private function addItems(Worksheet $sheet, mixed $items, int &$row, array $headers): void
    {
        foreach ($items as $item) {
            if (isset($item['children'])) {
                $sheet->setCellValue("A$row", str_repeat('    ', $item['indent']).$item['name']);
                $sheet->getStyle("A$row")->getFont()->setBold(true);
                ++$row;
                $this->addItems($sheet, $item['children'], $row, $headers);
            } else {
                $sheet->setCellValue("A$row", str_repeat('    ', $item['indent']).$item['name']);
                $sheet->getStyle("A$row")->getAlignment()->setIndent($item['indent']);

                for ($i = 1; $i < count($headers); ++$i) {
                    $col = $this->getExcelColumnName($i + 1);
                    $value = $item['values'][$i - 1];

                    if (is_numeric($value)) {
                        $sheet->setCellValue("{$col}{$row}", $value);
                        $sheet->getStyle("{$col}{$row}")->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                        $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    } else {
                        $sheet->setCellValue("{$col}{$row}", $value);
                    }
                }
                ++$row;
            }
        }
    }
    // endregion

    // region applyBorders
    private function applyBorders(Worksheet $sheet, array $headers, int $dataEndRow): void
    {
        $lastColumnIndex = count($headers);
        $lastColumn = $this->getExcelColumnName($lastColumnIndex);
        $sheet->getStyle("A4:{$lastColumn}{$dataEndRow}")->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THICK,
                ],
                'vertical' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
                'horizontal' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
    }
    // endregion

    // region autoFitColumns
    private function autoFitColumns(Worksheet $sheet, int $columnCount): void
    {
        for ($col = 1; $col <= $columnCount; ++$col) {
            $columnLetter = $this->getExcelColumnName($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }
    }
    // endregion

    // region saveSpreadsheetToString
    private function saveSpreadsheetToString(Spreadsheet $spreadsheet): string
    {
        $writer = new Xlsx($spreadsheet);
        $tempMemory = fopen('php://memory', 'w+');
        $writer->save($tempMemory);
        rewind($tempMemory);
        $excelContent = stream_get_contents($tempMemory);
        fclose($tempMemory);

        return $excelContent;
    }
    // endregion

    // region addToSections
    private function addToSections(array &$sections, mixed $categories, array $item): void
    {
        $sectionName = strtoupper(array_shift($categories));
        if (!isset($sections[$sectionName])) {
            $sections[$sectionName] = [];
        }
        $this->addToHierarchy($sections[$sectionName], $categories, $item);
    }
    // endregion

    // region addToHierarchy
    private function addToHierarchy(array &$parent, mixed $categories, array $item): void
    {
        if (empty($categories)) {
            $parent[] = $item;

            return;
        }
        $categoryName = array_shift($categories);

        foreach ($parent as &$child) {
            if (isset($child['name']) && $child['name'] == $categoryName && isset($child['children'])) {
                $this->addToHierarchy($child['children'], $categories, $item);

                return;
            }
        }

        $newCategory = [
            'name' => $categoryName,
            'indent' => $item['indent'] - count($categories) - 1,
            'children' => [],
        ];
        $parent[] = &$newCategory;
        $this->addToHierarchy($newCategory['children'], $categories, $item);
    }
    // endregion

    // region parseMonthToTimestamp
    private function parseMonthToTimestamp(int|string $monthString): int|false
    {
        $monthString = str_replace(' ', '', $monthString);

        if (preg_match('/^([A-Za-z]+)(\d{1,2}-\d{1,2}),(\d{4})$/', $monthString, $matches)) {
            $monthName = $matches[1];
            $year = $matches[3];
            $day = explode('-', $matches[2])[0];
            $dateString = "$monthName $day, $year";
        } elseif (preg_match('/^([A-Za-z]+)(\d{4})$/', $monthString, $matches)) {
            $monthName = $matches[1];
            $year = $matches[2];
            $day = '1';
            $dateString = "$monthName $day, $year";
        } else {
            return false;
        }

        return strtotime($dateString);
    }
    // endregion

    // region formatPeriods
    private function formatPeriods(array $months): array
    {
        $formatted = [];
        foreach ($months as $month) {
            if (preg_match('/^([A-Za-z]+)(\d{1,2}-\d{1,2}),(\d{4})$/', $month, $matches)) {
                $formatted[] = $matches[1].' '.$matches[2].', '.$matches[3];
            } elseif (preg_match('/^([A-Za-z]+)(\d{4})$/', $month, $matches)) {
                $formatted[] = $matches[1].' '.$matches[2];
            } else {
                $formatted[] = $month;
            }
        }

        return $formatted;
    }
    // endregion

    // region getExcelColumnName
    private function getExcelColumnName(int $columnNumber): string
    {
        $dividend = $columnNumber;
        $columnName = '';

        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $columnName = chr(65 + $modulo).$columnName;
            $dividend = intdiv($dividend - $modulo, 26);
        }

        return $columnName;
    }
    // endregion
}
