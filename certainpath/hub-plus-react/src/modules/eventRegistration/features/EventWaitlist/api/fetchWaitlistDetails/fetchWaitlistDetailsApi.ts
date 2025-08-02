import axiosInstance from "@/api/axiosInstance";
import {
  FetchWaitlistDetailsRequest,
  FetchWaitlistDetailsResponse,
} from "./types";

export const fetchWaitlistDetails = async (
  requestData: FetchWaitlistDetailsRequest,
): Promise<FetchWaitlistDetailsResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/waitlist`;

  const response = await axiosInstance.get<FetchWaitlistDetailsResponse>(url);

  return response.data;
};
