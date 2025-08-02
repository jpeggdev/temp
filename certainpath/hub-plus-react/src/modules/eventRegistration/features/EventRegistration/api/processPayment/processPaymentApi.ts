import axiosInstance from "@/api/axiosInstance";
import { ProcessPaymentRequest, ProcessPaymentResponse } from "./types";

export const processPayment = async (
  requestData: ProcessPaymentRequest,
): Promise<ProcessPaymentResponse> => {
  const response = await axiosInstance.post<ProcessPaymentResponse>(
    "/api/private/payments/process",
    requestData,
  );

  return response.data;
};
