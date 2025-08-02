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

class CreateTransactionListReportService
{
    public function generateReport(array $rawData): string
    {
        if (empty($rawData)) {
            throw new \InvalidArgumentException('No data provided to generate the report.');
        }

        $data = $this->processData($rawData);

        return $this->generateTransactionList($data);
    }

    private function processData(array $rawData): array
    {
        $firstRecord = $rawData[0];

        $data = [
            'companyName' => $firstRecord['tenant'],
            'reportTitle' => 'Transaction List',
            'reportDate' => 'As of '.date('F d, Y', strtotime($firstRecord['report_date'])),
            'transactions' => $rawData,
        ];

        return $data;
    }

    private function generateTransactionList(array $data): string
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transaction List');

        $this->setCompanyNameAndTitle($sheet, $data);
        $this->setHeaders($sheet);

        $sheet->freezePane('A5');
        $row = 5;

        foreach ($data['transactions'] as $transaction) {
            $this->addTransactionRow($sheet, $transaction, $row);
            ++$row;
        }

        $this->applyBorders($sheet, 4, $row - 1);
        $this->autoFitColumns($sheet);

        return $this->saveSpreadsheetToString($spreadsheet);
    }

    private function setCompanyNameAndTitle(Worksheet $sheet, array $data): void
    {
        $sheet->setCellValue('A1', $data['companyName']);
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', $data['reportTitle'].' '.$data['reportDate']);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function setHeaders(Worksheet $sheet): void
    {
        $headers = [
            'Date',
            'Transaction Type',
            'Num',
            'Posting',
            'Name',
            'Memo/Description',
            'Account',
            'Account Full Name',
            'Amount',
        ];

        $sheet->fromArray($headers, null, 'A4');
        $lastColumn = $this->getExcelColumnName(count($headers));

        $sheet->getStyle("A4:{$lastColumn}4")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A4:{$lastColumn}4")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
        $sheet->getStyle("A4:{$lastColumn}4")->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$lastColumn}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function addTransactionRow(Worksheet $sheet, array $transaction, int $row): void
    {
        $categories = json_decode($transaction['categories'], true);
        $accountFullName = implode(' > ', $categories);

        $dataRow = [
            $transaction['date'],
            $transaction['transactiontype'],
            $transaction['num'],
            $transaction['posting'],
            $transaction['name'],
            $transaction['memo'],
            $transaction['account'],
            $accountFullName,
            $transaction['amount'],
        ];

        $sheet->fromArray($dataRow, null, "A{$row}");

        // Format the date
        $sheet->getStyle("A{$row}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');

        // Format amount as currency
        $amountColumn = $this->getExcelColumnName(9); // 'I' column
        $sheet->getStyle("{$amountColumn}{$row}")->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
        $sheet->getStyle("{$amountColumn}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    private function applyBorders(Worksheet $sheet, int $startRow, int $endRow): void
    {
        $lastColumn = $this->getExcelColumnName(9); // 'I' column
        $sheet->getStyle("A4:{$lastColumn}{$endRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
    }

    private function autoFitColumns(Worksheet $sheet): void
    {
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

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
}
