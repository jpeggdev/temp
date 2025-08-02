import axiosInstance from "@/api/axiosInstance";
import { DuplicateEventResponse } from "./types";

export const duplicateEvent = async (
  eventId: number,
): Promise<DuplicateEventResponse> => {
  const url = `/api/private/event/${eventId}/duplicate`;
  const response = await axiosInstance.post<DuplicateEventResponse>(url);

  return response.data;
};
