import axiosInstance from "@/api/axiosInstance";
import { FetchEventDetailsRequest, FetchEventDetailsResponse } from "./types";

export const fetchEventDetails = async (
  requestData: FetchEventDetailsRequest,
): Promise<FetchEventDetailsResponse> => {
  const url = `/api/private/event-details/${requestData.uuid}`;
  const response = await axiosInstance.get<FetchEventDetailsResponse>(url);

  return response.data;
};
