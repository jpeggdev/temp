import axiosInstance from "@/api/axiosInstance";
import {
  ValidateVoucherNameRequest,
  ValidateVoucherNameResponse,
} from "./types";

export const validateVoucherName = async (
  requestData: ValidateVoucherNameRequest,
): Promise<ValidateVoucherNameResponse> => {
  const response = await axiosInstance.post<ValidateVoucherNameResponse>(
    "/api/private/event-voucher/validate-name",
    requestData,
  );

  return response.data;
};
