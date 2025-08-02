import axiosInstance from "@/api/axiosInstance";
import {
  UpdateEventCheckoutAttendeeWaitlistRequest,
  UpdateEventCheckoutAttendeeWaitlistResponse,
} from "./types";

export const updateEventCheckoutAttendeeWaitlist = async (
  eventCheckoutSessionUuid: string,
  requestData: UpdateEventCheckoutAttendeeWaitlistRequest,
): Promise<UpdateEventCheckoutAttendeeWaitlistResponse> => {
  const response =
    await axiosInstance.post<UpdateEventCheckoutAttendeeWaitlistResponse>(
      `/api/private/event-checkout-sessions/${eventCheckoutSessionUuid}/attendees/waitlist`,
      requestData,
    );

  return response.data;
};
