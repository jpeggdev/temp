import axiosInstance from "@/api/axiosInstance";
import { ToggleFavoriteEventResponse } from "./types";

export const toggleFavoriteEvent = async (
  eventUuid: string,
): Promise<ToggleFavoriteEventResponse> => {
  const url = `/api/private/events/${eventUuid}/favorite`;
  const response = await axiosInstance.post<ToggleFavoriteEventResponse>(url);

  return response.data;
};
