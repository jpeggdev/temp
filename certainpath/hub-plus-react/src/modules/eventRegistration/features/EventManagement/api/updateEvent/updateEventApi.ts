import axiosInstance from "@/api/axiosInstance";
import { UpdateEventRequest, UpdateEventResponse } from "./types";

export const updateEvent = async (
  eventId: number,
  requestBody: UpdateEventRequest,
): Promise<UpdateEventResponse> => {
  const url = `/api/private/event/${eventId}/update`;
  const response = await axiosInstance.patch<UpdateEventResponse>(
    url,
    requestBody,
  );

  return response.data;
};
