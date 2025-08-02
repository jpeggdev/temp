import { FetchEmailCampaignResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchEmailCampaign = async (
  id: number,
): Promise<FetchEmailCampaignResponse> => {
  const response = await axios.get<FetchEmailCampaignResponse>(
    `/api/private/email-campaign/${id}`,
  );
  return response.data;
};
