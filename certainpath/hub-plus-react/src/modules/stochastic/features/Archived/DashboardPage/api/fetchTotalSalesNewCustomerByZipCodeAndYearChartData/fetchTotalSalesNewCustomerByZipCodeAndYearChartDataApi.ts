import axios from "../../../../../../../api/axiosInstance";
import {
  FetchTotalSalesNewCustomerByZipCodeAndYearChartDataRequest,
  FetchTotalSalesNewCustomerByZipCodeAndYearChartDataResponse,
} from "./types";

export const fetchTotalSalesNewCustomerByZipCodeAndYearChartData = async (
  requestData?: FetchTotalSalesNewCustomerByZipCodeAndYearChartDataRequest,
): Promise<FetchTotalSalesNewCustomerByZipCodeAndYearChartDataResponse> => {
  const response =
    await axios.get<FetchTotalSalesNewCustomerByZipCodeAndYearChartDataResponse>(
      `/api/private/chart/total-sales-new-customer-by-zip-code-and-year`,
      { params: requestData },
    );

  return response.data;
};
