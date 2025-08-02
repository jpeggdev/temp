import axiosInstance from "@/api/axiosInstance";
import { GetEventCheckoutConfirmationDetailsResponse } from "./types";

export const getEventCheckoutConfirmationDetails = async (
  eventCheckoutSessionUuid: string,
): Promise<GetEventCheckoutConfirmationDetailsResponse> => {
  const response =
    await axiosInstance.get<GetEventCheckoutConfirmationDetailsResponse>(
      `/api/private/event-checkout-sessions/${eventCheckoutSessionUuid}/confirmation-details`,
    );

  return response.data;
};
