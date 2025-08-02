import { useState, useEffect } from "react";
import {
  FetchTotalSalesNewCustomerByZipCodeAndYearChartDataRequest,
  FetchTotalSalesNewCustomerByZipCodeAndYearChartDataResponse,
} from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesNewCustomerByZipCodeAndYearChartData/types";
import { fetchTotalSalesNewCustomerByZipCodeAndYearChartData } from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesNewCustomerByZipCodeAndYearChartData/fetchTotalSalesNewCustomerByZipCodeAndYearChartDataApi";

export function useTotalSalesNewCustomerByZipCodeAndYearChartData(
  requestData?: FetchTotalSalesNewCustomerByZipCodeAndYearChartDataRequest,
) {
  const [chartData, setChartData] =
    useState<FetchTotalSalesNewCustomerByZipCodeAndYearChartDataResponse | null>(
      null,
    );
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    fetchTotalSalesNewCustomerByZipCodeAndYearChartData(requestData)
      .then(
        (data: FetchTotalSalesNewCustomerByZipCodeAndYearChartDataResponse) => {
          setChartData(data);
          setLoading(false);
        },
      )
      .catch((error) => {
        setError(error.message);
        setLoading(false);
      });
  }, [requestData]);

  return {
    chartData,
    loading,
    error,
  };
}
