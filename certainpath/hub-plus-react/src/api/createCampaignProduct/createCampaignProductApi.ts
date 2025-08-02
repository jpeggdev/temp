import { CampaignProduct } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";
import axios from "@/api/axiosInstance";
import { CreateCampaignProductRequest } from "@/api/createCampaignProduct/types";

export const createCampaignProduct = async (
  product: CreateCampaignProductRequest,
): Promise<CampaignProduct> => {
  const response = await axios.post<CampaignProduct>(
    "/api/private/campaign-products",
    product,
  );
  return response.data;
};
