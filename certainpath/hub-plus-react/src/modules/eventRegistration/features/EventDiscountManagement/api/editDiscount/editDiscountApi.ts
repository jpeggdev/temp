import axios from "../../../../../../api/axiosInstance";
import { EditDiscountRequest, EditDiscountResponse } from "./types";

export const updateDiscount = async (
  id: number,
  requestData: EditDiscountRequest,
): Promise<EditDiscountResponse> => {
  const response = await axios.put<EditDiscountResponse>(
    `/api/private/event-discount/${id}/edit`,
    requestData,
  );
  return response.data;
};
