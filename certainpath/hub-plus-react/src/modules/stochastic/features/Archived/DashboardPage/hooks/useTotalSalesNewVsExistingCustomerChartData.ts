import { useState, useEffect } from "react";
import { fetchTotalSalesNewVsExistingCustomerChartData } from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesNewVsExistingCustomerChartData/fetchTotalSalesNewVsExistingCustomerChartDataApi";
import { FetchTotalSalesNewVsExistingCustomerChartDataResponse } from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesNewVsExistingCustomerChartData/types";

export function useTotalSalesNewVsExistingCustomerChartData() {
  const [chartData, setChartData] =
    useState<FetchTotalSalesNewVsExistingCustomerChartDataResponse | null>(
      null,
    );
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    fetchTotalSalesNewVsExistingCustomerChartData()
      .then((data: FetchTotalSalesNewVsExistingCustomerChartDataResponse) => {
        setChartData(data);
        setLoading(false);
      })
      .catch((error) => {
        setError(error.message);
        setLoading(false);
      });
  }, []);

  return { chartData, loading, error };
}
