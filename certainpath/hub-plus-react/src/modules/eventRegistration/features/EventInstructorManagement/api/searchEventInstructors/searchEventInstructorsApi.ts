import axiosInstance from "@/api/axiosInstance";
import {
  SearchEventInstructorsRequest,
  SearchEventInstructorsResponse,
} from "./types";

export const searchEventInstructors = async (
  params: SearchEventInstructorsRequest,
): Promise<SearchEventInstructorsResponse> => {
  const response = await axiosInstance.get<SearchEventInstructorsResponse>(
    "/api/private/event-instructors",
    { params },
  );

  return response.data;
};
