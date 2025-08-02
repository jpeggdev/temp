import axiosInstance from "@/api/axiosInstance";
import { FetchEventTypesRequest, FetchEventTypesResponse } from "./types";

export const fetchEventTypes = async (
  requestData: FetchEventTypesRequest,
): Promise<FetchEventTypesResponse> => {
  const response = await axiosInstance.get<FetchEventTypesResponse>(
    "/api/private/event-types",
    {
      params: requestData,
    },
  );
  return response.data;
};
