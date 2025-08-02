import axios from "../../../../../../api/axiosInstance";
import { CreateDiscountRequest, CreateDiscountResponse } from "./types";

export const createDiscount = async (
  requestData: CreateDiscountRequest,
): Promise<CreateDiscountResponse> => {
  const response = await axios.post<CreateDiscountResponse>(
    "/api/private/event-discount/create",
    requestData,
  );
  return response.data;
};
