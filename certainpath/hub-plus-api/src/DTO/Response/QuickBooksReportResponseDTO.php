<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\QuickBooksReport;

class QuickBooksReportResponseDTO
{
    public function __construct(
        public string $uuid,
        public string $date,
        public string $name,
    ) {
    }

    public static function fromQuickBooksReport(QuickBooksReport $report): self
    {
        $date = $report->getDate()->format('Y-m-d');
        $name = sprintf('%s-%s', $report->getReportType()->value, $date); // Combine reportType and date

        return new self(
            $report->getUuid(),
            $date,
            $name
        );
    }
}
