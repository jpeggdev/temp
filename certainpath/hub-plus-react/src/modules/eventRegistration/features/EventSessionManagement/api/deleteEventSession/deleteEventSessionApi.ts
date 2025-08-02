import axiosInstance from "@/api/axiosInstance";
import { DeleteEventSessionRequest, DeleteEventSessionResponse } from "./types";

export const deleteEventSession = async (
  requestData: DeleteEventSessionRequest,
): Promise<DeleteEventSessionResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/delete`;
  const response = await axiosInstance.delete<DeleteEventSessionResponse>(url);

  return response.data;
};
