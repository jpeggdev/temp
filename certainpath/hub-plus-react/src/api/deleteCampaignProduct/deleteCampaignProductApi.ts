import axios from "@/api/axiosInstance";

export const deleteCampaignProduct = async (
  id: string | number,
): Promise<void> => {
  await axios.delete(`/api/private/campaign-products/${id}`);
};
