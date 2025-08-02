import axiosInstance from "@/api/axiosInstance";
import {
  ResetEventCheckoutSessionReservationExpirationRequest,
  ResetEventCheckoutSessionReservationExpirationResponse,
} from "./types";

export const resetEventCheckoutSessionReservationExpiration = async (
  requestData: ResetEventCheckoutSessionReservationExpirationRequest,
): Promise<ResetEventCheckoutSessionReservationExpirationResponse> => {
  const response =
    await axiosInstance.post<ResetEventCheckoutSessionReservationExpirationResponse>(
      "/api/private/event-checkout-sessions/reset-reservation-expiration",
      requestData,
    );

  return response.data;
};
