import axiosInstance from "@/api/axiosInstance";
import {
  FetchEventSearchResultsRequest,
  FetchEventSearchResultsResponse,
} from "./types";

export const fetchEventSearchResults = async (
  requestData: FetchEventSearchResultsRequest,
): Promise<FetchEventSearchResultsResponse> => {
  const response = await axiosInstance.get<FetchEventSearchResultsResponse>(
    "/api/private/events/search",
    { params: requestData },
  );

  return response.data;
};
