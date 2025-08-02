import axios from "@/api/axiosInstance";
import { GetFileManagerMetaDataResponse } from "./types";

export const getFileManagerMetaData =
  async (): Promise<GetFileManagerMetaDataResponse> => {
    const response = await axios.get<GetFileManagerMetaDataResponse>(
      "/api/private/file-management/metadata",
    );
    return response.data;
  };
