<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\Enum\ReportType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class QuickBooksReportQueryDTO
{
    public function __construct(
        #[Assert\Choice(choices: ReportType::VALUES, message: 'Invalid report type')]
        public ?string $reportType = null,
        #[Assert\GreaterThanOrEqual(1)]
        public int $page = 1,
        #[Assert\GreaterThanOrEqual(1)]
        public int $pageSize = 10,
        #[Assert\Choice(choices: ['ASC', 'DESC'], message: 'Invalid sort order')]
        public string $sortOrder = 'ASC',
    ) {
    }
}
