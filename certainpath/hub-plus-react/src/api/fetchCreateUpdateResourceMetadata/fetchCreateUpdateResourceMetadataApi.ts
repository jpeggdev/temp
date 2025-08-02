import axios from "../axiosInstance";
import { FetchCreateUpdateResourceMetadataResponse } from "./types";

export const fetchCreateUpdateResourceMetadata =
  async (): Promise<FetchCreateUpdateResourceMetadataResponse> => {
    const response = await axios.get<FetchCreateUpdateResourceMetadataResponse>(
      "/api/private/create-update-resource-metadata",
    );
    return response.data;
  };
