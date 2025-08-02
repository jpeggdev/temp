import axios from "../axiosInstance";
import { FetchBatchesProspectsCsvRequest } from "@/api/fetchBatchesProspectsCsv/types";

export const fetchBatchesProspectsCsv = async (
  requestData?: FetchBatchesProspectsCsvRequest,
): Promise<Blob> => {
  const response = await axios.get<Blob>(`/api/private/batches/prospects/csv`, {
    responseType: "blob",
    headers: {
      Accept: "text/csv",
    },
    params: requestData,
  });

  return response.data;
};
