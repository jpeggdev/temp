import axiosInstance from "@/api/axiosInstance";
import { GetInProgressEventCheckoutSessionResponse } from "./types";

export const getInProgressEventCheckoutSession = async (
  eventSessionUuid: string,
): Promise<GetInProgressEventCheckoutSessionResponse> => {
  const response =
    await axiosInstance.get<GetInProgressEventCheckoutSessionResponse>(
      `/api/private/event-sessions/${eventSessionUuid}/checkout/in-progress`,
    );

  return response.data;
};
