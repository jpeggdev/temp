import axiosInstance from "@/api/axiosInstance";
import { GetEventCheckoutSessionDetailsResponse } from "./types";

export const getEventCheckoutSessionDetails = async (
  eventCheckoutSessionUuid: string,
): Promise<GetEventCheckoutSessionDetailsResponse> => {
  const response =
    await axiosInstance.get<GetEventCheckoutSessionDetailsResponse>(
      `/api/private/event-checkout-sessions/${eventCheckoutSessionUuid}/details`,
    );

  return response.data;
};
