import axiosInstance from "@/api/axiosInstance";
import { FetchEventTagsRequest, FetchEventTagsResponse } from "./types";

export const fetchEventTags = async (
  requestData: FetchEventTagsRequest,
): Promise<FetchEventTagsResponse> => {
  const response = await axiosInstance.get<FetchEventTagsResponse>(
    "/api/private/event-tags",
    {
      params: requestData,
    },
  );

  return response.data;
};
