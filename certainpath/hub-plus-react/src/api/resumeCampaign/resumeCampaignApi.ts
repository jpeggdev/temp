import axios from "../axiosInstance";
import { ResumeCampaignRequest, ResumeCampaignResponse } from "./types";

export const resumeCampaign = async (
  requestData: ResumeCampaignRequest,
): Promise<ResumeCampaignResponse> => {
  const response = await axios.patch<ResumeCampaignResponse>(
    `/api/private/campaign/resume`,
    requestData,
  );

  return response.data;
};
