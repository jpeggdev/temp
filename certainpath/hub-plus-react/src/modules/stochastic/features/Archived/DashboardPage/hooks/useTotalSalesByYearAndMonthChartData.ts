import { useState, useEffect } from "react";
import { FetchTotalSalesByYearAndMonthChartDataResponse } from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesByYearAndMonthChartData/types";
import { fetchTotalSalesByYearAndMonthChartData } from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchTotalSalesByYearAndMonthChartData/fetchTotalSalesByYearAndMonthChartDataApi";

export function useTotalSalesByYearAndMonthChartData() {
  const [chartData, setChartData] =
    useState<FetchTotalSalesByYearAndMonthChartDataResponse | null>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    fetchTotalSalesByYearAndMonthChartData()
      .then((data: FetchTotalSalesByYearAndMonthChartDataResponse) => {
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
