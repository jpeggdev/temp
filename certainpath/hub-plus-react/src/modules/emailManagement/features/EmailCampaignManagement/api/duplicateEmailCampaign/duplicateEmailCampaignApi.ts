import { DuplicateEmailCampaignResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const duplicateEmailCampaign = async (
  id: number,
): Promise<DuplicateEmailCampaignResponse> => {
  const response = await axios.delete<DuplicateEmailCampaignResponse>(
    `/api/private/email-campaign/${id}/duplicate`,
  );
  return response.data;
};
