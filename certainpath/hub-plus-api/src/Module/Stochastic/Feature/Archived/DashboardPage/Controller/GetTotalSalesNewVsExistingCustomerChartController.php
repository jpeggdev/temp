<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetTotalSalesNewVsExistingCustomerChartDataException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Service\GetTotalSalesNewVsExistingCustomerChartDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetTotalSalesNewVsExistingCustomerChartController extends ApiController
{
    public function __construct(
        private readonly GetTotalSalesNewVsExistingCustomerChartDataService $chartService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws FailedToGetTotalSalesNewVsExistingCustomerChartDataException
     */
    #[Route(
        '/chart/total-sales-new-vs-existing-customer',
        name: 'api_chart_total_sales_new_vs_existing_customer_get',
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
