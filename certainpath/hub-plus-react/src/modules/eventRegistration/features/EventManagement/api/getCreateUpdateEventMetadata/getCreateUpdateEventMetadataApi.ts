import axiosInstance from "@/api/axiosInstance";
import { GetCreateUpdateEventMetadataResponse } from "./types";

export const fetchCreateUpdateEventMetadata =
  async (): Promise<GetCreateUpdateEventMetadataResponse> => {
    const response =
      await axiosInstance.get<GetCreateUpdateEventMetadataResponse>(
        "/api/private/event-create-update-metadata",
      );
    return response.data;
  };
