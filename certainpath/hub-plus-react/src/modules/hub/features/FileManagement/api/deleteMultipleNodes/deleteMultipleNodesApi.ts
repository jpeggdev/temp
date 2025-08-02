import axios from "@/api/axiosInstance";
import { BulkDeleteNodesRequest, BulkDeleteNodesResponse } from "./types";

export const deleteMultipleNodes = async (
  requestData: BulkDeleteNodesRequest,
): Promise<BulkDeleteNodesResponse> => {
  const response = await axios.post<BulkDeleteNodesResponse>(
    `/api/private/file-management/nodes/bulk-delete`,
    requestData,
  );
  return response.data;
};
