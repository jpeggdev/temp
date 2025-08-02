import axios from "../axiosInstance";
import { GetBatchProspectsRequest, GetBatchProspectsResponse } from "./types";

export const getBatchProspects = async (
  batchId: number,
  requestData: GetBatchProspectsRequest,
): Promise<GetBatchProspectsResponse> => {
  const response = await axios.get<GetBatchProspectsResponse>(
    `/api/private/batch/${batchId}/prospects`,
    {
      params: requestData,
    },
  );
  return response.data;
};
