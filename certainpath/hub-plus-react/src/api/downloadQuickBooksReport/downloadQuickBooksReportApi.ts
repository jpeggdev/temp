import axios from "../axiosInstance";
import { DownloadQuickBooksReportRequest } from "./types";

export const downloadQuickBooksReport = async (
  requestData: DownloadQuickBooksReportRequest,
): Promise<Blob> => {
  const response = await axios.get<Blob>(
    `/api/private/quickbooks-report/${requestData.reportId}`,
    { responseType: "blob" },
  );

  return response.data;
};
