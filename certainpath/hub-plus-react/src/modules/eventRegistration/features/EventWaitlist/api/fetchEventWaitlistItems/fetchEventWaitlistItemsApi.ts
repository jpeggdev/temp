import axiosInstance from "@/api/axiosInstance";
import {
  FetchEventWaitlistItemsRequest,
  FetchEventWaitlistItemsResponse,
} from "./types";

export const fetchEventWaitlistItems = async (
  requestData: FetchEventWaitlistItemsRequest,
): Promise<FetchEventWaitlistItemsResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/waitlist/items`;

  const response = await axiosInstance.get<FetchEventWaitlistItemsResponse>(
    url,
    {
      params: {
        searchTerm: requestData.searchTerm,
        sortOrder: requestData.sortOrder,
        sortBy: requestData.sortBy,
        page: requestData.page,
        pageSize: requestData.pageSize,
      },
    },
  );

  return response.data;
};
