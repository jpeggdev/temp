import axios from "../../../../../../api/axiosInstance";
import { FetchDashboardDataRequest, FetchDashboardDataResponse } from "./types";

export const fetchStochasticDashboardData = async (
  requestData?: FetchDashboardDataRequest,
): Promise<FetchDashboardDataResponse> => {
  const response = await axios.get<FetchDashboardDataResponse>(
    `/api/private/stochastic/dashboard`,
    { params: requestData },
  );

  return response.data;
};
