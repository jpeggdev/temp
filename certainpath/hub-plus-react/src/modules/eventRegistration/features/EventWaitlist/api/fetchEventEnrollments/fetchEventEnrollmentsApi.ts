import axiosInstance from "@/api/axiosInstance";
import {
  FetchEventEnrollmentsRequest,
  FetchEventEnrollmentsResponse,
} from "./types";

export const fetchEventEnrollments = async (
  requestData: FetchEventEnrollmentsRequest,
): Promise<FetchEventEnrollmentsResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/enrollments`;

  const response = await axiosInstance.get<FetchEventEnrollmentsResponse>(url, {
    params: {
      searchTerm: requestData.searchTerm,
      sortOrder: requestData.sortOrder,
      sortBy: requestData.sortBy,
      page: requestData.page,
      pageSize: requestData.pageSize,
    },
  });

  return response.data;
};
