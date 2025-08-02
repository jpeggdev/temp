<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetTotalSalesByYearAndMonthChartDataException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Service\GetTotalSalesByYearAndMonthChartDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetTotalSalesByYearAndMonthChartController extends ApiController
{
    public function __construct(
        private readonly GetTotalSalesByYearAndMonthChartDataService $chartService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws FailedToGetTotalSalesByYearAndMonthChartDataException
     */
    #[Route(
        '/chart/total-sales-by-year-and-month',
        name: 'api_chart_total_sales_by_year_and_month_get',
        methods: ['GET'],
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $company = $loggedInUserDTO->getActiveCompany();

        $chartData = $this->chartService->getChartData(
            $company->getIntacctId()
        );

        return $this->createSuccessResponse($chartData);
    }
}
