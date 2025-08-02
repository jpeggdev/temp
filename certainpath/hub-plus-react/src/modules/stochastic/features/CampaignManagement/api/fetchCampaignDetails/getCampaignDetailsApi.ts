import axios from "@/api/axiosInstance";
import { GetCampaignDetailsResponse } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignDetails/types";

export const getCampaignDetails = async (
  campaignId: string,
): Promise<GetCampaignDetailsResponse> => {
  const response = await axios.get<GetCampaignDetailsResponse>(
    `/api/private/campaign/${campaignId}/details`,
  );
  return response.data;
};
