import { useState, useEffect } from "react";
import {
  FetchTotalSalesByZipCodeChartDataRequest,
  FetchTotalSalesByZipCodeChartDataResponse,
} from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesByZipCodeChartData/types";
import { fetchTotalSalesByZipCodeChartData } from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesByZipCodeChartData/fetchTotalSalesByZipCodeChartDataApi";

export function useTotalSalesByZipCodeChartData(
  requestData?: FetchTotalSalesByZipCodeChartDataRequest,
) {
  const [chartData, setChartData] =
    useState<FetchTotalSalesByZipCodeChartDataResponse | null>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    fetchTotalSalesByZipCodeChartData(requestData)
      .then((data: FetchTotalSalesByZipCodeChartDataResponse) => {
        setChartData(data);
        setLoading(false);
      })
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
