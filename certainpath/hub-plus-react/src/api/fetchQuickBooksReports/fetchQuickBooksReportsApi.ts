import axios from "../axiosInstance";
import {
  FetchQuickBooksReportsRequest,
  FetchQuickBooksReportsResponse,
} from "./types";

export const fetchQuickBooksReports = async (
  requestData: FetchQuickBooksReportsRequest,
): Promise<FetchQuickBooksReportsResponse> => {
  const response = await axios.get<FetchQuickBooksReportsResponse>(
    "/api/private/quickbooks-reports",
    {
      params: requestData,
    },
  );
  return response.data;
};
