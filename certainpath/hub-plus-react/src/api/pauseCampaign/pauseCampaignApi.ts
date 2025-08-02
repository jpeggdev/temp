import axios from "../axiosInstance";
import { PauseCampaignRequest, PauseCampaignResponse } from "./types";

export const pauseCampaign = async (
  requestData: PauseCampaignRequest,
): Promise<PauseCampaignResponse> => {
  const response = await axios.patch<PauseCampaignResponse>(
    `/api/private/campaign/pause`,
    requestData,
  );

  return response.data;
};
