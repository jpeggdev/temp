import {
  FetchEmailCampaignEventLogsRequest,
  FetchEmailCampaignEventLogsResponse,
} from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchEmailCampaignEventLogs = async (
  requestData: FetchEmailCampaignEventLogsRequest,
): Promise<FetchEmailCampaignEventLogsResponse> => {
  const response = await axios.get<FetchEmailCampaignEventLogsResponse>(
    "/api/private/email-campaign-activity-logs",
    { params: requestData },
  );
  return response.data;
};
