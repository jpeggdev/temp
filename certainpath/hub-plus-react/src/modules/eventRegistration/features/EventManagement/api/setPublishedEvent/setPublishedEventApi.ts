import axiosInstance from "@/api/axiosInstance";
import { SetPublishedEventRequest, SetPublishedEventResponse } from "./types";

export const setPublishedEvent = async (
  uuid: string,
  requestBody: SetPublishedEventRequest,
): Promise<SetPublishedEventResponse> => {
  const url = `/api/private/events/${uuid}/published`;
  const response = await axiosInstance.patch<SetPublishedEventResponse>(
    url,
    requestBody,
  );

  return response.data;
};
