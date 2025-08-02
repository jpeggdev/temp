import axiosInstance from "@/api/axiosInstance";
import { DeleteEventTagRequest, DeleteEventTagResponse } from "./types";

export const deleteEventTag = async (
  requestData: DeleteEventTagRequest,
): Promise<DeleteEventTagResponse> => {
  const url = `/api/private/event/tag/${requestData.id}/delete`;
  const response = await axiosInstance.delete<DeleteEventTagResponse>(url);

  return response.data;
};
