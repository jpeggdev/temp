<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\DTO\Query\GetTotalSalesNewCustomerByZipCodeAndYearChartDTO;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Exception\FailedToGetTotalSalesNewCustomerByZipCodeAndYearChartDataException;
use App\Module\Stochastic\Feature\Archived\DashboardPage\Service\GetTotalSalesNewCustomerByZipCodeAndYearChartDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetTotalSalesNewCustomerByZipCodeAndYearChartController extends ApiController
{
    public function __construct(
        private readonly GetTotalSalesNewCustomerByZipCodeAndYearChartDataService $chartService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws FailedToGetTotalSalesNewCustomerByZipCodeAndYearChartDataException
     */
    #[Route(
        '/chart/total-sales-new-customer-by-zip-code-and-year',
        name: 'api_chart_total_sales_new_customer_customer_by_zip_code_and_year_get',
        methods: ['GET'],
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        #[MapQueryString] GetTotalSalesNewCustomerByZipCodeAndYearChartDTO $chartDTO = new GetTotalSalesNewCustomerByZipCodeAndYearChartDTO(),
    ): Response {
        $company = $loggedInUserDTO->getActiveCompany();
        $chartData = $this->chartService->getChartData(
            $company->getIntacctId(),
            $chartDTO,
        );

        return $this->createSuccessResponse($chartData);
    }
}
