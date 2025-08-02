import {
  FetchEmailCampaignStatusesRequest,
  FetchEmailCampaignStatusesResponse,
} from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchEmailCampaignStatuses = async (
  requestData: FetchEmailCampaignStatusesRequest,
): Promise<FetchEmailCampaignStatusesResponse> => {
  const response = await axios.get<FetchEmailCampaignStatusesResponse>(
    "/api/private/email-campaign-statuses",
    { params: requestData },
  );
  return response.data;
};
