import axios from "../axiosInstance";
import { GetCampaignBatchesRequest, GetCampaignBatchesResponse } from "./types";

export const getCampaignBatches = async (
  campaignId: number,
  requestData: GetCampaignBatchesRequest,
): Promise<GetCampaignBatchesResponse> => {
  const response = await axios.get<GetCampaignBatchesResponse>(
    `/api/private/campaign/${campaignId}/batches`,
    {
      params: requestData,
    },
  );
  return response.data;
};
