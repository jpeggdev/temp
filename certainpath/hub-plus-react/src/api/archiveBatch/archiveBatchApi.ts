import axios from "../axiosInstance";
import { ArchiveBatchRequest, ArchiveBatchResponse } from "./types";

export const archiveBatch = async (
  requestData: ArchiveBatchRequest,
): Promise<ArchiveBatchResponse> => {
  const response = await axios.patch<ArchiveBatchResponse>(
    `/api/private/batch/archive`,
    requestData,
  );

  return response.data;
};
