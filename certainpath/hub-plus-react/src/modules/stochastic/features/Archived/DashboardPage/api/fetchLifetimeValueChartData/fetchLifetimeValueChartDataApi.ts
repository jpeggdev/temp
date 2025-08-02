import axios from "../../../../../../../api/axiosInstance";
import {
  FetchLifetimeValueChartDataRequest,
  FetchLifetimeValueChartDataResponse,
} from "./types";

export const fetchLifetimeValueChartData = async (
  requestData?: FetchLifetimeValueChartDataRequest,
): Promise<FetchLifetimeValueChartDataResponse> => {
  const response = await axios.get<FetchLifetimeValueChartDataResponse>(
    `/api/private/chart/lifetime-value`,
    { params: requestData },
  );

  return response.data;
};
