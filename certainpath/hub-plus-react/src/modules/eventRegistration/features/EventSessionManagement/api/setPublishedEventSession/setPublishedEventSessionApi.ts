import axiosInstance from "@/api/axiosInstance";
import {
  SetPublishedEventSessionRequest,
  SetPublishedEventSessionResponse,
} from "./types";

export const setPublishedEventSession = async (
  requestData: SetPublishedEventSessionRequest,
): Promise<SetPublishedEventSessionResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/published`;

  const body = { isPublished: requestData.isPublished };

  const response = await axiosInstance.patch<SetPublishedEventSessionResponse>(
    url,
    body,
  );

  return response.data;
};
