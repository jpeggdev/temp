<?php

namespace App\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetCustomersAverageInvoiceComparisonChartDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetPercentageOfNewCustomersByZipCodeDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByYearAndMonthDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByZipCodeDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesNewVsExistingCustomerDataException;
use App\Exceptions\DomainException\StochasticDashboard\FailedToLifetimeValueDataException;

readonly class GetStochasticDashboardDataService
{
    public function __construct(
        private LifetimeValueDataService $lifetimeValueDataService,
        private LifetimeValueByTierDataService $lifetimeValueByTierDataService,
        private TotalSalesByZipCodeDataService $totalSalesByZipCodeDataService,
        private TotalSalesByYearAndMonthDataService $totalSalesByYearAndMonthDataService,
        private TotalSalesNewVsExistingCustomerDataService $totalSalesNewVsExistingCustomerDataService,
        private CustomersAverageInvoiceComparisonDataService $customersAverageInvoiceComparisonDataService,
        private PercentageOfNewCustomersByZipCodeDataService $percentageOfNewCustomersByZipCodeDataService,
        private TotalSalesNewCustomerByZipCodeAndYearDataService $totalSalesNewCustomerByZipCodeAndYearDataService,
        private PercentageOfNewCustomersChangeByZipCodeDataService $percentageOfNewCustomersChangeByZipCodeDataService,
    ) {
    }

    /**
     * @throws FailedToLifetimeValueDataException
     * @throws FailedToGetTotalSalesByZipCodeDataException
     * @throws FailedToGetTotalSalesByYearAndMonthDataException
     * @throws FailedToGetTotalSalesNewVsExistingCustomerDataException
     * @throws FailedToGetPercentageOfNewCustomersByZipCodeDataException
     * @throws FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException
     * @throws FailedToGetCustomersAverageInvoiceComparisonChartDataException
     */
    public function getData(StochasticDashboardDTO $dto): array
    {
        if ($dto->scope === StochasticDashboardDTO::SCOPE_SALES) {
            $totalSalesByZipCodeData = $this->totalSalesByZipCodeDataService
                ->getData($dto);
            $totalSalesByYearAndMonthData = $this->totalSalesByYearAndMonthDataService
                ->getData($dto);
            $totalSalesNewVsExistingCustomerData = $this->totalSalesNewVsExistingCustomerDataService
                ->getData($dto);
            $totalSalesNewCustomerByZipCodeAndYearData = $this->totalSalesNewCustomerByZipCodeAndYearDataService
                ->getData($dto);

            return [
                'totalSalesByZipCodeData' => $totalSalesByZipCodeData,
                'totalSalesByYearAndMonthData' => $totalSalesByYearAndMonthData,
                'totalSalesNewVsExistingCustomerData' => $totalSalesNewVsExistingCustomerData,
                'totalSalesNewCustomerByZipCodeAndYearData' => $totalSalesNewCustomerByZipCodeAndYearData,
            ];
        }

        if ($dto->scope === StochasticDashboardDTO::SCOPE_CUSTOMERS) {
            $lifetimeValueData = $this->lifetimeValueDataService
                ->getData($dto);
            $lifetimeValueByTierData = $this->lifetimeValueByTierDataService
                ->getData($dto);
            $percentageOfNewCustomersByZipCodeData = $this->percentageOfNewCustomersByZipCodeDataService
                ->getData($dto);
            $customersAverageInvoiceComparisonData = $this->customersAverageInvoiceComparisonDataService
                ->getData($dto);
            $percentageOfNewCustomersChangeByZipCodeData = $this->percentageOfNewCustomersChangeByZipCodeDataService
                ->getData($dto);

            return [
                'lifetimeValueData' => $lifetimeValueData,
                'lifetimeValueByTierData' => $lifetimeValueByTierData,
                'percentageOfNewCustomersByZipCodeData' => $percentageOfNewCustomersByZipCodeData,
                'customersAverageInvoiceComparisonData' => $customersAverageInvoiceComparisonData,
                'percentageOfNewCustomersChangeByZipCodeData' => $percentageOfNewCustomersChangeByZipCodeData,
            ];
        }

        return [];
    }
}
