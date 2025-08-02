import axiosInstance from "@/api/axiosInstance";
import { FetchEventFilterMetadataResponse } from "./types";

export const fetchEventFilterMetadata =
  async (): Promise<FetchEventFilterMetadataResponse> => {
    const url = "/api/private/events/filter-metadata";
    const response =
      await axiosInstance.get<FetchEventFilterMetadataResponse>(url);

    return response.data;
  };
