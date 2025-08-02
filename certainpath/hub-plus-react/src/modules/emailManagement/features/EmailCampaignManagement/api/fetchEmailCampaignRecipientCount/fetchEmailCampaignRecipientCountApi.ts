import {
  FetchEmailCampaignRecipientCountRequest,
  FetchEmailCampaignRecipientCountResponse,
} from "./types";
import axios from "@/api/axiosInstance";

export const fetchEmailCampaignRecipientCount = async (
  requestData: FetchEmailCampaignRecipientCountRequest,
): Promise<FetchEmailCampaignRecipientCountResponse> => {
  const response = await axios.get<FetchEmailCampaignRecipientCountResponse>(
    "/api/private/email-campaign-recipient-count",
    { params: requestData },
  );
  return response.data;
};
