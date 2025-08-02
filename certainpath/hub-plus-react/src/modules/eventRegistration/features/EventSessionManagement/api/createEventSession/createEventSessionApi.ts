import axiosInstance from "@/api/axiosInstance";
import { CreateEventSessionRequest, CreateEventSessionResponse } from "./types";

export const createEventSession = async (
  requestBody: CreateEventSessionRequest,
): Promise<CreateEventSessionResponse> => {
  const response = await axiosInstance.post<CreateEventSessionResponse>(
    "/api/private/event-sessions",
    requestBody,
  );
  return response.data;
};
