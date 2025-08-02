import axiosInstance from "@/api/axiosInstance";
import {
  FetchSingleEventTypeRequest,
  FetchSingleEventTypeResponse,
} from "./types";

export const fetchSingleEventType = async (
  requestData: FetchSingleEventTypeRequest,
): Promise<FetchSingleEventTypeResponse> => {
  const url = `/api/private/event/type/${requestData.id}`;
  const response = await axiosInstance.get<FetchSingleEventTypeResponse>(url);
  return response.data;
};
