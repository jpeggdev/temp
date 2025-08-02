import axiosInstance from "@/api/axiosInstance";
import { CreateEventTagRequest, CreateEventTagResponse } from "./types";

export const createEventTag = async (
  requestBody: CreateEventTagRequest,
): Promise<CreateEventTagResponse> => {
  const response = await axiosInstance.post<CreateEventTagResponse>(
    "/api/private/event/tag/create",
    requestBody,
  );

  return response.data;
};
