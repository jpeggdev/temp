import {
  FetchEmailCampaignEventLogsMetadataRequest,
  FetchEmailCampaignEventLogsMetadataResponse,
} from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogMetadata/types";
import axios from "../../../../../../api/axiosInstance";

export const fetchEmailCampaignEventLogsMetadata = async (
  requestData: FetchEmailCampaignEventLogsMetadataRequest,
): Promise<FetchEmailCampaignEventLogsMetadataResponse> => {
  const response = await axios.get<FetchEmailCampaignEventLogsMetadataResponse>(
    "/api/private/email-campaign-activity-logs/metadata",
    { params: requestData },
  );
  return response.data;
};
