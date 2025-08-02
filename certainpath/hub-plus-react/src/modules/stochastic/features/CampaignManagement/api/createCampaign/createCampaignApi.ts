import axios from "../../../../../../api/axiosInstance";
import { CreateCampaignRequest, CreateCampaignResponse } from "./types";

export const createCampaign = async (
  requestData: CreateCampaignRequest,
): Promise<CreateCampaignResponse> => {
  const response = await axios.post<CreateCampaignResponse>(
    "/api/private/campaign/create",
    requestData,
  );
  return response.data;
};
