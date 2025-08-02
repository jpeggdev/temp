import axiosInstance from "@/api/axiosInstance";
import { FetchEventSessionsRequest, FetchEventSessionsResponse } from "./types";

export const fetchEventSessions = async (
  requestData: FetchEventSessionsRequest,
): Promise<FetchEventSessionsResponse> => {
  const response = await axiosInstance.get<FetchEventSessionsResponse>(
    "/api/private/event-sessions",
    { params: requestData },
  );
  return response.data;
};
