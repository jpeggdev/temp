import axios from "../../../../../../api/axiosInstance";
import { FetchCampaignResponse } from "./types";

export const fetchCampaign = async (
  id: number,
): Promise<FetchCampaignResponse> => {
  const response = await axios.get<FetchCampaignResponse>(
    `/api/private/campaign/${id}`,
  );
  return response.data;
};
