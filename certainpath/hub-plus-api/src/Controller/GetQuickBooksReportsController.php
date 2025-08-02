<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\QuickBooksReportQueryDTO;
use App\Service\QuickBooksReportQueryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetQuickBooksReportsController extends ApiController
{
    public function __construct(private readonly QuickBooksReportQueryService $reportQueryService)
    {
    }

    #[Route('/quickbooks-reports', name: 'api_quickbooks_reports_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] QuickBooksReportQueryDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $reportsData = $this->reportQueryService->getReports(
            $loggedInUserDTO->getActiveCompany(),
            $queryDto
        );

        return $this->createSuccessResponse(
            $reportsData['reports'],
            $reportsData['totalCount']
        );
    }
}
