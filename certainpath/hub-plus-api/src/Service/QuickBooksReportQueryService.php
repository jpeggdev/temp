<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\QuickBooksReportQueryDTO;
use App\DTO\Response\QuickBooksReportResponseDTO;
use App\Entity\Company;
use App\Repository\QuickBooksReportRepository;

readonly class QuickBooksReportQueryService
{
    public function __construct(private QuickBooksReportRepository $reportRepository)
    {
    }

    /**
     * @return array{
     *     reports: QuickBooksReportResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getReports(Company $company, QuickBooksReportQueryDTO $queryDto): array
    {
        $reports = $this->reportRepository->findReports(
            $company->getIntacctId(),
            $queryDto->reportType,
            $queryDto->page,
            $queryDto->pageSize,
            $queryDto->sortOrder
        );

        $reportDtos = array_map(fn ($report) => QuickBooksReportResponseDTO::fromQuickBooksReport($report), $reports['reports']);

        return [
            'reports' => $reportDtos,
            'totalCount' => $reports['totalCount'],
        ];
    }
}
