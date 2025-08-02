import axios from "../../../../../../../api/axiosInstance";
import {
  FetchTotalSalesByZipCodeChartDataRequest,
  FetchTotalSalesByZipCodeChartDataResponse,
} from "./types";

export const fetchTotalSalesByZipCodeChartData = async (
  requestData?: FetchTotalSalesByZipCodeChartDataRequest,
): Promise<FetchTotalSalesByZipCodeChartDataResponse> => {
  const response = await axios.get<FetchTotalSalesByZipCodeChartDataResponse>(
    `/api/private/chart/total-sales-by-zip-code`,
    { params: requestData },
  );

  return response.data;
};
