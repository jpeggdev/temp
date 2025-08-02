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

class CreateProfitAndLossReportService
{
    public function generateReport(array $rawData): string
    {
        $data = $this->processData($rawData);

        return $this->generateProfitAndLoss($data);
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
            'reportTitle' => 'Profit and Loss',
            'reportDate' => 'As of '.date('F d, Y', strtotime($firstRecord['report_date'])),
        ];

        $aggregatedData = $this->aggregateData($rawData);
        $sections = [];
        $sectionSubtotals = [];

        foreach ($aggregatedData as $entry) {
            $categories = $entry['categories'];
            $total = $entry['total'];

            $item = [
                'name' => array_pop($categories),
                'value' => $total,
                'indent' => count($categories),
            ];

            $this->addToSections($sections, $categories, $item);

            $sectionName = strtoupper($categories[0] ?? $item['name']);
            if (!isset($sectionSubtotals[$sectionName])) {
                $sectionSubtotals[$sectionName] = 0;
            }
            $sectionSubtotals[$sectionName] += $total;
        }

        $data['sections'] = $sections;
        $data['sectionSubtotals'] = $sectionSubtotals;

        return $data;
    }
    // endregion

    // region aggregateData
    private function aggregateData(array $rawData): array
    {
        $aggregatedData = [];

        foreach ($rawData as $record) {
            $categories = json_decode($record['categories'], true);
            $categories[] = $record['account'];

            $total = floatval($record['total']);

            $key = implode('>', $categories);

            if (!isset($aggregatedData[$key])) {
                $aggregatedData[$key] = [
                    'categories' => $categories,
                    'total' => $total,
                ];
            } else {
                $aggregatedData[$key]['total'] += $total;
            }
        }

        return $aggregatedData;
    }
    // endregion

    // region generateProfitAndLoss
    private function generateProfitAndLoss(array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Profit and Loss');

        $this->setCompanyNameAndTitle($sheet, $data);
        $this->setHeaders($sheet);

        $sheet->freezePane('A5');
        $row = 5;

        $sectionColors = $this->getSectionColors();

        $totalIncome = 0;
        $totalExpenses = 0;

        foreach ($data['sections'] as $sectionName => $items) {
            $color = $sectionColors[strtoupper($sectionName)] ?? 'FFFFFF';
            $this->addSection($sheet, $sectionName, $items, $color, $data, $row, $totalIncome, $totalExpenses);
        }

        $netIncome = $totalIncome - $totalExpenses;

        $this->addTotalIncomeExpensesNetIncome($sheet, $totalIncome, $totalExpenses, $netIncome, $row);

        $this->applyBorders($sheet, 4, $row - 1);
        $this->autoFitColumns($sheet);

        return $this->saveSpreadsheetToString($spreadsheet);
    }
    // endregion

    // region setCompanyNameAndTitle
    private function setCompanyNameAndTitle(Worksheet $sheet, array $data): void
    {
        $sheet->setCellValue('A1', $data['companyName']);
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', $data['reportTitle'].' '.$data['reportDate']);
        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    // endregion

    // region setHeaders
    private function setHeaders(Worksheet $sheet): void
    {
        $headers = ['Description', 'Total'];
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:B4')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A4:B4')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
        $sheet->getStyle('A4:B4')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    // endregion

    // region getSectionColors
    private function getSectionColors(): array
    {
        return [
            'INCOME' => 'D9E1F2',
            'EXPENSES' => 'F2DCDB',
            'COST OF GOODS SOLD' => 'E2EFDA',
            'OTHER INCOME' => 'FFF2CC',
            'OTHER EXPENSES' => 'E4DFEC',
        ];
    }
    // endregion

    // region addSection
    private function addSection(
        Worksheet $sheet,
        int|string $sectionName,
        mixed $items,
        string $fillColor,
        array $data,
        int &$row,
        float &$totalIncome,
        float &$totalExpenses,
    ): void {
        $this->addSectionTitle($sheet, $sectionName, $fillColor, $row);

        $this->addItems($sheet, $items, $row);

        $subtotal = $data['sectionSubtotals'][strtoupper($sectionName)] ?? 0;
        $this->addSubtotalRow($sheet, $sectionName, $subtotal, $row);

        if ('INCOME' === strtoupper($sectionName)) {
            $totalIncome += $subtotal;
        } elseif ('EXPENSES' === strtoupper($sectionName)) {
            $totalExpenses += $subtotal;
        }

        $row += 2; // Add an extra row for spacing
    }
    // endregion

    // region addSectionTitle
    private function addSectionTitle(Worksheet $sheet, int|string $sectionName, string $fillColor, int &$row): void
    {
        $sheet->setCellValue("A$row", strtoupper($sectionName));
        $sheet->mergeCells("A$row:B$row");
        $sheet->getStyle("A$row:B$row")
            ->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A$row:B$row")
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fillColor);
        $sheet->getStyle("A$row:B$row")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        ++$row;
    }
    // endregion

    // region addSubtotalRow
    private function addSubtotalRow(Worksheet $sheet, int|string $sectionName, mixed $subtotal, int &$row): void
    {
        $sheet->setCellValue("A{$row}", 'Total '.$sectionName);
        $sheet->setCellValue("B{$row}", $subtotal);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        ++$row;
    }
    // endregion

    // region addItems
    private function addItems(Worksheet $sheet, mixed $items, int &$row): void
    {
        foreach ($items as $item) {
            if (isset($item['children'])) {
                $sheet->setCellValue("A$row", str_repeat('    ', $item['indent']).$item['name']);
                $sheet->getStyle("A$row")->getFont()->setBold(true);
                ++$row;
                $this->addItems($sheet, $item['children'], $row);
            } else {
                $sheet->setCellValue("A$row", str_repeat('    ', $item['indent']).$item['name']);
                $sheet->getStyle("A$row")->getAlignment()->setIndent($item['indent']);

                $value = $item['value'];
                if (is_numeric($value)) {
                    $sheet->setCellValue("B{$row}", $value);
                    $sheet->getStyle("B{$row}")->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                } else {
                    $sheet->setCellValue("B{$row}", $value);
                }
                ++$row;
            }
        }
    }
    // endregion

    // region addTotalIncomeExpensesNetIncome
    private function addTotalIncomeExpensesNetIncome(
        Worksheet $sheet,
        float $totalIncome,
        float $totalExpenses,
        float $netIncome,
        int &$row,
    ): void {
        $this->addSummaryRow($sheet, 'Total Income', $totalIncome, $row);
        $this->addSummaryRow($sheet, 'Total Expenses', $totalExpenses, $row);
        $this->addSummaryRow($sheet, 'Net Income', $netIncome, $row);
    }
    // endregion

    // region addSummaryRow
    private function addSummaryRow(Worksheet $sheet, string $label, float $amount, int &$row): void
    {
        $sheet->setCellValue("A{$row}", $label);
        $sheet->setCellValue("B{$row}", $amount);
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        ++$row;
    }
    // endregion

    // region applyBorders
    private function applyBorders(Worksheet $sheet, int $startRow, int $endRow): void
    {
        $sheet->getStyle("A4:B{$endRow}")->applyFromArray([
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
    private function autoFitColumns(Worksheet $sheet): void
    {
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }
    // endregion

    // region saveSpreadsheetToString
    private function saveSpreadsheetToString(Spreadsheet $spreadsheet): string
    {
        $writer = new Xlsx($spreadsheet);
        $tempMemory = fopen('php://memory', 'w+'); // Use memory stream
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
}
