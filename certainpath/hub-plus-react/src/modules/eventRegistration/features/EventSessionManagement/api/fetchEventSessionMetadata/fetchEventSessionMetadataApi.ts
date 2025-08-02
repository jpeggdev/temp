import axiosInstance from "@/api/axiosInstance";
import { GetCreateUpdateEventSessionMetadataResponse } from "./types";

export const fetchEventSessionMetadata =
  async (): Promise<GetCreateUpdateEventSessionMetadataResponse> => {
    const url = "/api/private/event-create-update-session-metadata";
    const response =
      await axiosInstance.get<GetCreateUpdateEventSessionMetadataResponse>(url);

    return response.data;
  };
