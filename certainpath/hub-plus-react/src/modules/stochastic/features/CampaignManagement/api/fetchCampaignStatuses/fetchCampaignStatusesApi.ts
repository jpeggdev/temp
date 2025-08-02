import axios from "../../../../../../api/axiosInstance";
import { FetchCampaignStatusesResponse } from "./types";

export const fetchCampaignStatuses =
  async (): Promise<FetchCampaignStatusesResponse> => {
    const response = await axios.get<FetchCampaignStatusesResponse>(
      "/api/private/campaign-statuses",
    );
    return response.data;
  };
