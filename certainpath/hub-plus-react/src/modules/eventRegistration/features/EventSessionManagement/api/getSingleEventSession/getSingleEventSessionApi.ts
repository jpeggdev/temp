import axiosInstance from "@/api/axiosInstance";
import { FetchSingleEventSessionResponse } from "./types";

export const fetchSingleEventSession = async (
  uuid: string,
): Promise<FetchSingleEventSessionResponse> => {
  const response = await axiosInstance.get<FetchSingleEventSessionResponse>(
    `/api/private/event-sessions/${uuid}`,
  );

  return response.data;
};
