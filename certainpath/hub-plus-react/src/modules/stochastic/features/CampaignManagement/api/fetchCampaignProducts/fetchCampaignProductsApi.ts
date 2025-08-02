import axios from "../../../../../../api/axiosInstance";
import { FetchCampaignProductsResponse } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";

export const fetchCampaignProducts =
  async (): Promise<FetchCampaignProductsResponse> => {
    const response = await axios.get<FetchCampaignProductsResponse>(
      "/api/private/campaign-products",
    );
    return response.data;
  };
