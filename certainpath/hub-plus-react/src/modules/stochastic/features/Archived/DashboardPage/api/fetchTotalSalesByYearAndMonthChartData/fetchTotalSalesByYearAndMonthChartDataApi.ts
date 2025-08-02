import axios from "../../../../../../../api/axiosInstance";
import { FetchTotalSalesByYearAndMonthChartDataResponse } from "./types";

export const fetchTotalSalesByYearAndMonthChartData =
  async (): Promise<FetchTotalSalesByYearAndMonthChartDataResponse> => {
    const response =
      await axios.get<FetchTotalSalesByYearAndMonthChartDataResponse>(
        "/api/private/chart/total-sales-by-year-and-month",
      );
    return response.data;
  };
