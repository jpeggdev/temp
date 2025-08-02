import axios from "../axiosInstance";
import { GetResourceFilterMetaDataAPIResponse } from "./types";

export const getResourceFilterMetaData =
  async (): Promise<GetResourceFilterMetaDataAPIResponse> => {
    const response = await axios.get<GetResourceFilterMetaDataAPIResponse>(
      "/api/private/resources/filter-metadata",
    );
    return response.data;
  };
