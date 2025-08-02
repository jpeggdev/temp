import axiosInstance from "@/api/axiosInstance";
import {
  UpdateEventViewCountRequest,
  UpdateEventViewCountResponse,
} from "./types";

export const updateEventViewCount = async (
  requestData: UpdateEventViewCountRequest,
): Promise<UpdateEventViewCountResponse> => {
  const url = `/api/private/events/${requestData.uuid}/views`;

  const response = await axiosInstance.post<UpdateEventViewCountResponse>(url);

  return response.data;
};
