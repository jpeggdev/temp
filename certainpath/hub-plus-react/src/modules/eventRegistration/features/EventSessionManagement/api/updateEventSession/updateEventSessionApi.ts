import axiosInstance from "@/api/axiosInstance";
import { UpdateEventSessionRequest, UpdateEventSessionResponse } from "./types";

export const updateEventSession = async (
  uuid: string,
  requestBody: UpdateEventSessionRequest,
): Promise<UpdateEventSessionResponse> => {
  const url = `/api/private/event-sessions/${uuid}`;
  const response = await axiosInstance.put<UpdateEventSessionResponse>(
    url,
    requestBody,
  );

  return response.data;
};
