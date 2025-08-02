import axiosInstance from "@/api/axiosInstance";
import {
  CreateEventCheckoutSessionRequest,
  CreateEventCheckoutSessionResponse,
} from "./types";

export const createEventCheckoutSession = async (
  requestData: CreateEventCheckoutSessionRequest,
): Promise<CreateEventCheckoutSessionResponse> => {
  const response = await axiosInstance.post<CreateEventCheckoutSessionResponse>(
    "/api/private/event-checkout-sessions/create",
    requestData,
  );

  return response.data;
};
