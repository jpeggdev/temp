import {
  FetchEmailCampaignsRequest,
  FetchEmailCampaignsResponse,
} from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchEmailCampaigns = async (
  requestData: FetchEmailCampaignsRequest,
): Promise<FetchEmailCampaignsResponse> => {
  const response = await axios.get<FetchEmailCampaignsResponse>(
    "/api/private/email-campaigns",
    { params: requestData },
  );
  return response.data;
};
