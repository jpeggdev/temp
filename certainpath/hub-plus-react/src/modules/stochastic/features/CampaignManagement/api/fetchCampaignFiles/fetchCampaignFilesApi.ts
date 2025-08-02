import { FetchCampaignFilesRequest, FetchCampaignFilesResponse } from "./types";
import { axiosInstance } from "../../../../../../api/axiosInstance";

export const fetchCampaignFiles = async (
  campaignId: number,
  requestData: FetchCampaignFilesRequest,
): Promise<FetchCampaignFilesResponse> => {
  const response = await axiosInstance.get<FetchCampaignFilesResponse>(
    `/api/private/campaign/${campaignId}/files`,
    {
      params: {
        ...requestData,
      },
    },
  );

  return response.data;
};
