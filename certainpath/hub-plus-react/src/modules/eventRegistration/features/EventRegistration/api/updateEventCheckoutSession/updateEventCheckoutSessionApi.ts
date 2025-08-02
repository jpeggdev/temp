import axiosInstance from "@/api/axiosInstance";
import {
  UpdateEventCheckoutSessionRequest,
  UpdateEventCheckoutSessionResponse,
} from "./types";

export const updateEventCheckoutSession = async (
  eventCheckoutSessionUuid: string,
  requestData: UpdateEventCheckoutSessionRequest,
): Promise<UpdateEventCheckoutSessionResponse> => {
  const response =
    await axiosInstance.patch<UpdateEventCheckoutSessionResponse>(
      `/api/private/event-checkout-sessions/${eventCheckoutSessionUuid}/update`,
      requestData,
    );

  return response.data;
};
