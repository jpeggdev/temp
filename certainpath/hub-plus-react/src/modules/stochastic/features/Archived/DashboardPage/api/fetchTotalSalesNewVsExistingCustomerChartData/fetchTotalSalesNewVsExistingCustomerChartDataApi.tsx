import axios from "../../../../../../../api/axiosInstance";
import { FetchTotalSalesNewVsExistingCustomerChartDataResponse } from "./types";

export const fetchTotalSalesNewVsExistingCustomerChartData =
  async (): Promise<FetchTotalSalesNewVsExistingCustomerChartDataResponse> => {
    const response =
      await axios.get<FetchTotalSalesNewVsExistingCustomerChartDataResponse>(
        "/api/private/chart/total-sales-new-vs-existing-customer",
      );
    return response.data;
  };
