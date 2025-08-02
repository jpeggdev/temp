import axios from "../axiosInstance";
import { UpdateCampaignRequest, UpdateCampaignResponse } from "./types";

export const updateCampaign = async (
  campaignId: number,
  requestData: UpdateCampaignRequest,
): Promise<UpdateCampaignResponse> => {
  const response = await axios.patch<UpdateCampaignResponse>(
    `/api/private/campaign/${campaignId}`,
    requestData,
  );
  return response.data;
};
