import axiosInstance from "@/api/axiosInstance";
import { DeleteEventTypeRequest, DeleteEventTypeResponse } from "./types";

export const deleteEventType = async (
  requestData: DeleteEventTypeRequest,
): Promise<DeleteEventTypeResponse> => {
  const url = `/api/private/event/type/${requestData.id}/delete`;
  const response = await axiosInstance.delete<DeleteEventTypeResponse>(url);
  return response.data;
};
