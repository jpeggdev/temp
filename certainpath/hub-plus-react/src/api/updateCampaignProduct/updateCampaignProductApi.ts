import { CampaignProduct } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";
import axios from "@/api/axiosInstance";
import { CreateCampaignProductRequest } from "@/api/createCampaignProduct/types";

export const updateCampaignProduct = async (
  id: string | number,
  product: Partial<CreateCampaignProductRequest>,
): Promise<CampaignProduct> => {
  const response = await axios.put<CampaignProduct>(
    `/api/private/campaign-products/${id}`,
    product,
  );
  return response.data;
};
