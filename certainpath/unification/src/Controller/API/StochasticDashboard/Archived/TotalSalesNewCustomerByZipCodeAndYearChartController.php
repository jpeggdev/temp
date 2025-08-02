<?php

namespace App\Controller\API\StochasticDashboard\Archived;

use App\Controller\API\ApiController;
use App\DTO\Query\Chart\FilterableChartDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesNewVsExistingCustomerDataException;
use App\Services\StochasticDashboard\TotalSalesNewCustomerByZipCodeAndYearDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class TotalSalesNewCustomerByZipCodeAndYearChartController extends ApiController
{
    public function __construct(
        private readonly TotalSalesNewCustomerByZipCodeAndYearDataService $chartService,
    ) {
    }

    /**
     * @throws FailedToGetTotalSalesNewVsExistingCustomerDataException
     */
    #[Route(
        '/api/chart/total-sales-new-customer-by-zip-code-and-year',
        name: 'api_chart_total_sales_new_customer_by_zip_code_and_year_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] FilterableChartDTO $chartDTO = new FilterableChartDTO,
    ): Response {
        $data = $this->chartService->getData($chartDTO);

        return $this->createJsonSuccessResponse($data);
    }
}
