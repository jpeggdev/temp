import axiosInstance from "@/api/axiosInstance";
import { FetchEventsRequest, FetchEventsResponse } from "./types";

export const fetchEvents = async (
  requestData: FetchEventsRequest,
): Promise<FetchEventsResponse> => {
  const response = await axiosInstance.get<FetchEventsResponse>(
    "/api/private/events",
    {
      params: requestData,
    },
  );

  return response.data;
};
