import axiosInstance from "@/api/axiosInstance";
import { EditEventTagRequest, EditEventTagResponse } from "./types";

export const editEventTag = async (
  requestData: EditEventTagRequest,
): Promise<EditEventTagResponse> => {
  const url = `/api/private/event/tag/${requestData.id}/edit`;
  const response = await axiosInstance.patch<EditEventTagResponse>(url, {
    name: requestData.name,
  });

  return response.data;
};
