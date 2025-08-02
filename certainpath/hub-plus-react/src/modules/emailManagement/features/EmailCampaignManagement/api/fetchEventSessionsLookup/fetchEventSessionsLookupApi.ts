import {
  FetchEventSessionsLookupRequest,
  FetchEventSessionsLookupResponse,
} from "./types";
import axiosInstance from "@/api/axiosInstance";

export const fetchEventSessionsLookup = async (
  requestData: FetchEventSessionsLookupRequest,
): Promise<FetchEventSessionsLookupResponse> => {
  const params = {
    ...requestData,
  };

  const response = await axiosInstance.get<FetchEventSessionsLookupResponse>(
    "/api/private/event-sessions/lookup",
    { params },
  );

  return response.data;
};
