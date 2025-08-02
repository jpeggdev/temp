import axiosInstance from "@/api/axiosInstance";
import { CreateEventTypeRequest, CreateEventTypeResponse } from "./types";

export const createEventType = async (
  requestBody: CreateEventTypeRequest,
): Promise<CreateEventTypeResponse> => {
  const response = await axiosInstance.post<CreateEventTypeResponse>(
    "/api/private/event/type/create",
    requestBody,
  );
  return response.data;
};
