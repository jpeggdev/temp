import { useState, useEffect } from "react";
import {
  FetchLifetimeValueChartDataRequest,
  FetchLifetimeValueChartDataResponse,
} from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchLifetimeValueChartData/types";
import { fetchLifetimeValueChartData } from "@/modules/stochastic/features/Archived/DashboardPage/api/fetchLifetimeValueChartData/fetchLifetimeValueChartDataApi";

export function useLifetimeValueChartData(
  requestData?: FetchLifetimeValueChartDataRequest,
) {
  const [chartData, setChartData] =
    useState<FetchLifetimeValueChartDataResponse | null>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    fetchLifetimeValueChartData(requestData)
      .then((data: FetchLifetimeValueChartDataResponse) => {
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
