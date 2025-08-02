import axios from "../axiosInstance";
import { StopCampaignRequest, StopCampaignResponse } from "./types";

export const stopCampaign = async (
  requestData: StopCampaignRequest,
): Promise<StopCampaignResponse> => {
  const response = await axios.patch<StopCampaignResponse>(
    `/api/private/campaign/stop`,
    requestData,
  );

  return response.data;
};
