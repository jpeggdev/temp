import axios from "../axiosInstance";
import { DownloadFileRequest } from "./types";

export const downloadFile = async (
  requestData: DownloadFileRequest,
): Promise<Blob> => {
  const response = await axios.get<Blob>(
    `/api/private/file/${requestData.fileUuid}/download`,
    {
      responseType: "blob",
    },
  );

  return response.data;
};
