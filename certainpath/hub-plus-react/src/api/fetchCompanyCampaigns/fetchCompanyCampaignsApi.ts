import {
  FetchCompanyCampaignsRequest,
  FetchCompanyCampaignsResponse,
} from "./types";
import axios from "../axiosInstance";

export const fetchCompanyCampaigns = async (
  requestData: FetchCompanyCampaignsRequest,
): Promise<FetchCompanyCampaignsResponse> => {
  const response = await axios.get<FetchCompanyCampaignsResponse>(
    "/api/private/company/campaigns",
    { params: requestData },
  );
  return response.data;
};
