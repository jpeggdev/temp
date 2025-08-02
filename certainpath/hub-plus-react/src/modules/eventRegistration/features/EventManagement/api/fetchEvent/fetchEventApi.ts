import axiosInstance from "@/api/axiosInstance";
import { FetchEventRequest, FetchEventResponse } from "./types";

export const fetchEvent = async (
  requestData: FetchEventRequest,
): Promise<FetchEventResponse> => {
  const url = `/api/private/event/${requestData.uuid}`;
  const response = await axiosInstance.get<FetchEventResponse>(url);
  return response.data;
};
