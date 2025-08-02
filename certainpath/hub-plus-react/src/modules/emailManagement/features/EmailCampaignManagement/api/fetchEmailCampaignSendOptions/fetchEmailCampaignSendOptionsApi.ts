import { FetchEmailCampaignSendOptionsResponse } from "./types";
import { axiosInstance } from "../../../../../../api/axiosInstance";

export const fetchEmailCampaignSendOptions =
  async (): Promise<FetchEmailCampaignSendOptionsResponse> => {
    const response =
      await axiosInstance.get<FetchEmailCampaignSendOptionsResponse>(
        "/api/private/email-campaign-send-options",
      );
    return response.data;
  };
