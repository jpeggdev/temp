import axiosInstance from "@/api/axiosInstance";
import {
  ValidateDiscountCodeRequest,
  ValidateDiscountCodeResponse,
} from "./types";

export const validateDiscountCode = async (
  requestData: ValidateDiscountCodeRequest,
): Promise<ValidateDiscountCodeResponse> => {
  const response = await axiosInstance.post<ValidateDiscountCodeResponse>(
    "/api/private/event-discount/validate-code",
    requestData,
  );

  return response.data;
};
