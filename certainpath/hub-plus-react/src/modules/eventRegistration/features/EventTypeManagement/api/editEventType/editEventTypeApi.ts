import axiosInstance from "@/api/axiosInstance";
import { EditEventTypeRequest, EditEventTypeResponse } from "./types";

export const editEventType = async (
  requestData: EditEventTypeRequest,
): Promise<EditEventTypeResponse> => {
  const url = `/api/private/event/type/${requestData.id}/edit`;
  const response = await axiosInstance.patch<EditEventTypeResponse>(url, {
    name: requestData.name,
    description: requestData.description,
    isActive: requestData.isActive,
  });
  return response.data;
};
