import axiosInstance from "@/api/axiosInstance";
import {
  FetchSingleEventTagRequest,
  FetchSingleEventTagResponse,
} from "./types";

export const fetchSingleEventTag = async (
  requestData: FetchSingleEventTagRequest,
): Promise<FetchSingleEventTagResponse> => {
  const url = `/api/private/event/tag/${requestData.id}`;
  const response = await axiosInstance.get<FetchSingleEventTagResponse>(url);

  return response.data;
};
