import { FetchEventsLookupRequest, FetchEventsLookupResponse } from "./types";
import axiosInstance from "@/api/axiosInstance";

export const fetchEventsLookup = async (
  requestData: FetchEventsLookupRequest,
): Promise<FetchEventsLookupResponse> => {
  const params = {
    ...requestData,
  };

  const response = await axiosInstance.get<FetchEventsLookupResponse>(
    "/api/private/events/lookup",
    { params },
  );

  return response.data;
};
