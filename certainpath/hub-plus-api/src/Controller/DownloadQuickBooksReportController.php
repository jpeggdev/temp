<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\QuickBooksReport;
use App\Service\DownloadQuickBooksReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DownloadQuickBooksReportController extends ApiController
{
    public function __construct(private readonly DownloadQuickBooksReportService $downloadQuickBooksReportService)
    {
    }

    #[Route('/quickbooks-report/{uuid}', name: 'api_quickbooks_report_download', methods: ['GET'])]
    public function __invoke(QuickBooksReport $quickBooksReport): StreamedResponse
    {
        return $this->downloadQuickBooksReportService->downloadReport($quickBooksReport);
    }
}
