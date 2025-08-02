import { DeleteEmailCampaignResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const deleteEmailCampaign = async (
  id: number,
): Promise<DeleteEmailCampaignResponse> => {
  const response = await axios.delete<DeleteEmailCampaignResponse>(
    `/api/private/email-campaign/${id}/delete`,
  );
  return response.data;
};
