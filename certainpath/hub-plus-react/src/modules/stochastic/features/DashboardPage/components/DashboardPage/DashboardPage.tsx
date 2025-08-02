import React from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { StochasticDashboardFilters } from "@/modules/stochastic/features/DashboardPage/components/DashboardFilters/StochasticDashboardFilters";
import { useStochasticDashboard } from "@/modules/stochastic/features/DashboardPage/hooks/useStochasticDashboard";
import TotalSalesNewVsExistingCustomerChart from "@/modules/stochastic/features/DashboardPage/components/Chart/TotalSalesNewVsExistingCustomerChart/TotalSalesNewVsExistingCustomerChart";
import LifetimeValueChart from "../Chart/LifetimeValueChart/LifetimeValueChart";
import TotalSalesNewCustomerByZipCodeAndYearChart from "@/modules/stochastic/features/DashboardPage/components/Chart/TotalSalesNewCustomerByZipCodeAndYearChart/TotalSalesNewCustomerByZipCodeAndYearChart";
import TotalSalesByYearAndMonthChart from "@/modules/stochastic/features/DashboardPage/components/Chart/TotalSalesByYearAndMonthChart/TotalSalesByYearAndMonthChart";
import TotalSalesByZipCodeChart from "../Chart/TotalSalesByZipCodeChart/TotalSalesByZipCodeChart";
import { PercentageOfNewCustomersByZipCodeChart } from "@/modules/stochastic/features/DashboardPage/components/Chart/PercentageOfNewCustomersByZipCodeChart/PercentageOfNewCustomersByZipCodeChart";
import CustomersAverageInvoiceComparisonChart from "@/modules/stochastic/features/DashboardPage/components/Chart/CustomersAverageInvoiceComparisonChart/CustomersAverageInvoiceComparisonChart";
import LifetimeValueByTierChart from "@/modules/stochastic/features/DashboardPage/components/Chart/LifetimeValueByTierChart/LifetimeValueByTierChart";
import { PercentageOfNewCustomersChangeByZipCodeTable } from "@/modules/stochastic/features/DashboardPage/components/Table/PercentageOfNewCustomersChangeByZipCodeTable/PercentageOfNewCustomersChangeByZipCodeTable";
import { LifetimeValueByTierTable } from "@/modules/stochastic/features/DashboardPage/components/Table/LifetimeValueByTierTable/LifetimeValueByTierTable";
import { DashboardTabSelector } from "@/modules/stochastic/features/DashboardPage/components/DashboardTabSelector/DashboardTabSelector";
import LifetimeValueByTierChartSkeleton from "@/modules/stochastic/features/DashboardPage/components/Chart/LifetimeValueByTierChart/LifetimeValueByTierChartSkeleton";
import LifetimeValueChartSkeleton from "@/modules/stochastic/features/DashboardPage/components/Chart/LifetimeValueChart/LifetimeValueChartSkeleton";
import { PercentageOfNewCustomersByZipCodeChartSkeleton } from "@/modules/stochastic/features/DashboardPage/components/Chart/PercentageOfNewCustomersByZipCodeChart/PercentageOfNewCustomersByZipCodeChartSkeleton";
import { TotalSalesByYearAndMonthChartSkeleton } from "@/modules/stochastic/features/DashboardPage/components/Chart/TotalSalesByYearAndMonthChart/TotalSalesByYearAndMonthChartSkeleton";
import { TotalSalesByZipCodeChartSkeleton } from "@/modules/stochastic/features/DashboardPage/components/Chart/TotalSalesByZipCodeChart/TotalSalesByZipCodeChartSkeleton";
import TotalSalesNewCustomerByZipCodeAndYearChartSkeleton from "@/modules/stochastic/features/DashboardPage/components/Chart/TotalSalesNewCustomerByZipCodeAndYearChart/TotalSalesNewCustomerByZipCodeAndYearChartSkeleton";
import TotalSalesNewVsExistingCustomerChartSkeleton from "@/modules/stochastic/features/DashboardPage/components/Chart/TotalSalesNewVsExistingCustomerChart/TotalSalesNewVsExistingCustomerChartSkeleton";
import { CustomersAverageInvoiceComparisonChartSkeleton } from "@/modules/stochastic/features/DashboardPage/components/Chart/CustomersAverageInvoiceComparisonChart/CustomersAverageInvoiceComparisonChartSkeleton";
import { LifetimeValueByTierTableSkeleton } from "@/modules/stochastic/features/DashboardPage/components/Table/LifetimeValueByTierTable/LifetimeValueByTierTableSkeleton";
import { PercentageOfNewCustomersChangeByZipCodeTableSkeleton } from "../Table/PercentageOfNewCustomersChangeByZipCodeTable/PercentageOfNewCustomersChangeByZipCodeTableSkeleton";

export default function DashboardPage() {
  const {
    dashboardData,
    dashboardFiltersForm,
    loading,
    activeTab,
    setActiveTab,
  } = useStochasticDashboard();

  const renderSalesCharts = () => (
    <>
      <TotalSalesNewVsExistingCustomerChart
        initialData={dashboardData.totalSalesNewVsExistingCustomerData || []}
      />
      <TotalSalesByYearAndMonthChart
        initialData={dashboardData.totalSalesByYearAndMonthData || []}
      />
      <TotalSalesByZipCodeChart
        initialData={dashboardData.totalSalesByZipCodeData || []}
      />
      <TotalSalesNewCustomerByZipCodeAndYearChart
        initialData={
          dashboardData.totalSalesNewCustomerByZipCodeAndYearData || []
        }
      />
    </>
  );

  const renderSalesSkeletons = () => (
    <>
      <TotalSalesNewVsExistingCustomerChartSkeleton />
      <TotalSalesByYearAndMonthChartSkeleton />
      <TotalSalesByZipCodeChartSkeleton />
      <TotalSalesNewCustomerByZipCodeAndYearChartSkeleton />
    </>
  );

  const renderCustomerCharts = () => (
    <>
      <LifetimeValueChart initialData={dashboardData.lifetimeValueData || []} />
      <LifetimeValueByTierChart
        initialData={dashboardData.lifetimeValueByTierData}
      />
      <LifetimeValueByTierTable
        initialData={dashboardData.lifetimeValueByTierData}
      />
      <PercentageOfNewCustomersByZipCodeChart
        initialData={dashboardData.percentageOfNewCustomersByZipCodeData || []}
      />
      <PercentageOfNewCustomersChangeByZipCodeTable
        initialData={
          dashboardData.percentageOfNewCustomersChangeByZipCodeData || []
        }
      />
      <CustomersAverageInvoiceComparisonChart
        initialData={dashboardData.customersAverageInvoiceComparisonData || []}
      />
    </>
  );

  const renderCustomerSkeletons = () => (
    <>
      <LifetimeValueChartSkeleton />
      <LifetimeValueByTierChartSkeleton />
      <LifetimeValueByTierTableSkeleton />
      <PercentageOfNewCustomersByZipCodeChartSkeleton />
      <PercentageOfNewCustomersChangeByZipCodeTableSkeleton />
      <CustomersAverageInvoiceComparisonChartSkeleton />
    </>
  );

  return (
    <MainPageWrapper loading={loading} title="Stochastic Dashboard">
      <div className="bg-white dark:bg-secondary-dark min-h-screen transition-colors duration-200">
        <DashboardTabSelector
          activeTab={activeTab}
          setActiveTab={setActiveTab}
        />
        <StochasticDashboardFilters chartFiltersForm={dashboardFiltersForm} />
        <div className="max-w-7xl mx-auto space-y-8">
          {loading
            ? activeTab === "sales"
              ? renderSalesSkeletons()
              : renderCustomerSkeletons()
            : activeTab === "sales"
              ? renderSalesCharts()
              : renderCustomerCharts()}
        </div>
      </div>
    </MainPageWrapper>
  );
}
