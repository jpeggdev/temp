import { DeleteDiscountResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const deleteDiscount = async (
  id: number,
): Promise<DeleteDiscountResponse> => {
  const response = await axios.delete<DeleteDiscountResponse>(
    `/api/private/event-discount/${id}/delete`,
  );
  return response.data;
};
