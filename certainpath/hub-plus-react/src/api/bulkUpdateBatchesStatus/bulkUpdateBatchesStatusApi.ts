import axios from "../axiosInstance";
import {
  BulkUpdateBatchStatusRequest,
  BulkUpdateBatchStatusResponse,
} from "./types";

export const bulkUpdateBatchesStatus = async (
  requestData: BulkUpdateBatchStatusRequest,
): Promise<BulkUpdateBatchStatusResponse> => {
  const response = await axios.patch<BulkUpdateBatchStatusResponse>(
    `/api/private/batches/bulk-update-status`,
    requestData,
  );
  return response.data;
};
