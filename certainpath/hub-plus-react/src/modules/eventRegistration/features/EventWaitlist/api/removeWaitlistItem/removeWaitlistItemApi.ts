import axiosInstance from "@/api/axiosInstance";
import { RemoveWaitlistItemRequest, RemoveWaitlistItemResponse } from "./types";

export const removeWaitlistItem = async (
  requestData: RemoveWaitlistItemRequest,
): Promise<RemoveWaitlistItemResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/waitlist/remove`;

  const response = await axiosInstance.post<RemoveWaitlistItemResponse>(url, {
    eventWaitlistId: requestData.eventWaitlistId,
  });

  return response.data;
};
