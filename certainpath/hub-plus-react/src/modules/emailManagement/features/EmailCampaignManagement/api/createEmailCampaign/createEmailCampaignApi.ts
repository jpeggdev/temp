import axios from "../../../../../../api/axiosInstance";
import {
  CreateEmailCampaignRequest,
  CreateEmailCampaignResponse,
} from "./types";

export const createEmailCampaign = async (
  requestData: CreateEmailCampaignRequest,
): Promise<CreateEmailCampaignResponse> => {
  const response = await axios.post<CreateEmailCampaignResponse>(
    "/api/private/email-campaign/create",
    requestData,
  );
  return response.data;
};
