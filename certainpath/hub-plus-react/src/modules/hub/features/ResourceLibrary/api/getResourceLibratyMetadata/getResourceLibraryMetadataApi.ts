import axios from "../../../../../../api/axiosInstance";
import { GetResourceLibraryMetadataResponse } from "./types";

export const getResourceLibraryMetadata =
  async (): Promise<GetResourceLibraryMetadataResponse> => {
    const response = await axios.get<GetResourceLibraryMetadataResponse>(
      `/api/private/resource-library-metadata`,
    );
    return response.data;
  };
