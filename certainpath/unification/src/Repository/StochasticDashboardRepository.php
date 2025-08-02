<?php

namespace App\Repository;

use App\dto\Query\Chart\StochasticDashboardDTO;
use App\StatementBuilder\StochasticDashboard\Chart\CustomersAverageInvoiceComparisonStatementBuilder;
use App\StatementBuilder\StochasticDashboard\Chart\LifetimeValueByTierStatementBuilder;
use App\StatementBuilder\StochasticDashboard\Chart\LifetimeValueStatementBuilder;
use App\StatementBuilder\StochasticDashboard\Chart\PercentageOfNewCustomersByZipCodeStatementBuilder;
use App\StatementBuilder\StochasticDashboard\Chart\TotalSalesByYearAndMonthStatementBuilder;
use App\StatementBuilder\StochasticDashboard\Chart\TotalSalesByZipCodeStatementBuilder;
use App\StatementBuilder\StochasticDashboard\Chart\TotalSalesNewCustomerByZipCodeAndYearStatementBuilder;
use App\StatementBuilder\StochasticDashboard\Chart\TotalSalesNewVsExistingCustomerStatementBuilder;
use App\StatementBuilder\StochasticDashboard\Table\PercentageOfNewCustomersChangeByZipCodeStatementBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;

readonly class StochasticDashboardRepository
{
    public function __construct(
        private LifetimeValueStatementBuilder
            $lifetimeValueStatementBuilder,
        private TotalSalesByZipCodeStatementBuilder
            $totalSalesByZipCodeStatementBuilder,
        private LifetimeValueByTierStatementBuilder
            $lifetimeValueByTierStatementBuilder,
        private TotalSalesByYearAndMonthStatementBuilder
            $totalSalesByYearAndMonthStatementBuilder,
        private TotalSalesNewVsExistingCustomerStatementBuilder
            $totalSalesNewVsExistingCustomerStatementBuilder,
        private PercentageOfNewCustomersByZipCodeStatementBuilder
            $percentageOfNewCustomersByZipCodeStatementBuilder,
        private CustomersAverageInvoiceComparisonStatementBuilder
            $customersAverageInvoiceComparisonStatementBuilder,
        private TotalSalesNewCustomerByZipCodeAndYearStatementBuilder
            $totalSalesNewCustomerByZipCodeAndYearStatementBuilder,
        private PercentageOfNewCustomersChangeByZipCodeStatementBuilder
            $percentageOfNewCustomersChangeByZipCodeTableStatementBuilder,
    ) {
    }

    /**
     * @throws Exception
     */
    public function fetchTotalSalesByZipCodeChartData(
        StochasticDashboardDTO $dto
    ): ArrayCollection {
        $result = $this->totalSalesByZipCodeStatementBuilder
            ->createStatement($dto)
            ->executeQuery()
            ->fetchAllAssociative();

        return new ArrayCollection($result);
    }

    /**
     * @throws Exception
     */
    public function getTotalSalesNewCustomerByZipCodeAndYearChartData(
        StochasticDashboardDTO $dto
    ): ArrayCollection {
        $result = $this->totalSalesNewCustomerByZipCodeAndYearStatementBuilder
            ->createStatement($dto)
            ->executeQuery()
            ->fetchAllAssociative();

        return new ArrayCollection($result);
    }

    /**
     * @throws Exception
     */
    public function fetchTotalSalesNewVsExistingCustomerChartData(
        StochasticDashboardDTO $dto
    ): ArrayCollection {
        $result = $this->totalSalesNewVsExistingCustomerStatementBuilder
            ->createStatement($dto)
            ->executeQuery()
            ->fetchAllAssociative();

        return new ArrayCollection($result);
    }

    /**
     * @throws Exception
     */
    public function fetchLifetimeValueChartData(
        StochasticDashboardDTO $dto
    ): ArrayCollection {
        $result = $this->lifetimeValueStatementBuilder
            ->createStatement($dto)
            ->executeQuery()
            ->fetchAllAssociative();

        return new ArrayCollection($result);
    }

    /**
     * @throws Exception
     */
    public function fetchLifetimeValueByTierChartData(
        StochasticDashboardDTO $dto
    ): ArrayCollection {
        $result = $this->lifetimeValueByTierStatementBuilder
            ->createStatement($dto)
            ->executeQuery()
            ->fetchAllAssociative();

        return new ArrayCollection($result);
    }

    /**
     * @throws Exception
     */
    public function fetchTotalSalesByYearAndMonthData(
        StochasticDashboardDTO $dto
    ): ArrayCollection {
        $result = $this->totalSalesByYearAndMonthStatementBuilder
            ->createStatement($dto)
            ->executeQuery()
            ->fetchAllAssociative();

        return new ArrayCollection($result);
    }

    /**
     * @throws Exception
     */
    public function getPercentageOfNewCustomersByZipCodeChartData(
        StochasticDashboardDTO $dto
    ): ArrayCollection {
         $result = $this->percentageOfNewCustomersByZipCodeStatementBuilder
             ->createStatement($dto)
             ->executeQuery()
             ->fetchAllAssociative();

         return new ArrayCollection($result);
    }

    /**
     * @throws Exception
     */
    public function getCustomersAverageInvoiceComparisonChartData(
        StochasticDashboardDTO $dto
    ): ArrayCollection {
        $result = $this->customersAverageInvoiceComparisonStatementBuilder
            ->createStatement($dto)
            ->executeQuery()
            ->fetchAllAssociative();

        return new ArrayCollection($result);
    }

    /**
     * @throws Exception
     */
    public function getPercentageOfNewCustomersByZipCodeTableData(
        StochasticDashboardDTO $dto
    ): ArrayCollection {
        $result = $this->percentageOfNewCustomersChangeByZipCodeTableStatementBuilder
            ->createStatement($dto)
            ->executeQuery()
            ->fetchAllAssociative();

        return new ArrayCollection($result);
    }
}
