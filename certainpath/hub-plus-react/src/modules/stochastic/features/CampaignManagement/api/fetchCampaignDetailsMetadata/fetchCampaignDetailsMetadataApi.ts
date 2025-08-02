import axios from "../../../../../../api/axiosInstance";
import { FetchCampaignDetailsMetadataResponse } from "./types";

export const fetchCampaignDetailsMetadata =
  async (): Promise<FetchCampaignDetailsMetadataResponse> => {
    const response = await axios.get<FetchCampaignDetailsMetadataResponse>(
      "/api/private/campaign-details-metadata",
    );
    return response.data;
  };
