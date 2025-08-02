import axiosInstance from "@/api/axiosInstance";
import { DeleteEventResponse } from "./types";

export const deleteEvent = async (
  eventId: number,
): Promise<DeleteEventResponse> => {
  const url = `/api/private/event/${eventId}/delete`;
  const response = await axiosInstance.delete<DeleteEventResponse>(url);

  return response.data;
};
