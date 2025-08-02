<?php

namespace App\Controller\API\StochasticDashboard;

use App\Controller\API\ApiController;
use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetCustomersAverageInvoiceComparisonChartDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetPercentageOfNewCustomersByZipCodeDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByYearAndMonthDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByZipCodeDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesNewVsExistingCustomerDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToLifetimeValueDataException;
use App\Services\StochasticDashboard\GetStochasticDashboardDataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetStochasticDashboardController extends ApiController
{
    public function __construct(
        private readonly GetStochasticDashboardDataService $getStochasticDashboardDataService,
    ) {
    }

    /**
     * @throws FailedToLifetimeValueDataException
     * @throws FailedToGetTotalSalesByZipCodeDataException
     * @throws FailedToGetTotalSalesByYearAndMonthDataException
     * @throws FailedToGetTotalSalesNewVsExistingCustomerDataException
     * @throws FailedToGetPercentageOfNewCustomersByZipCodeDataException
     * @throws FailedToGetCustomersAverageInvoiceComparisonChartDataException
     * @throws FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException
     */
    #[Route(
        '/api/stochastic/dashboard',
        name: 'api_stochastic_dashboard_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] StochasticDashboardDTO $dto = new StochasticDashboardDTO()
    ): Response {
        $data = $this->getStochasticDashboardDataService->getData($dto);

        return $this->createJsonSuccessResponse($data);
    }
}
