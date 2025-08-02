import axiosInstance from "@/api/axiosInstance";
import { CreateEventRequest, CreateEventResponse } from "./types";

export const createEvent = async (
  requestData: CreateEventRequest,
): Promise<CreateEventResponse> => {
  const response = await axiosInstance.post<CreateEventResponse>(
    "/api/private/event/create",
    requestData,
  );

  return response.data;
};
