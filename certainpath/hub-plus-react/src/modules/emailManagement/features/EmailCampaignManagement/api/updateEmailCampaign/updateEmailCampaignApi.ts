import axios from "../../../../../../api/axiosInstance";
import {
  UpdateEmailCampaignRequest,
  CreateEmailCampaignResponse,
} from "./types";

export const updateEmailCampaign = async (
  id: number,
  requestData: UpdateEmailCampaignRequest,
): Promise<CreateEmailCampaignResponse> => {
  const response = await axios.put<CreateEmailCampaignResponse>(
    `/api/private/email-campaign/${id}/update`,
    requestData,
  );
  return response.data;
};
