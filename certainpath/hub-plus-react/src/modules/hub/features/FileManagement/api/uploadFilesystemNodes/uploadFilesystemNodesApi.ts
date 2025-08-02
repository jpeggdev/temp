import axios from "@/api/axiosInstance";
import { AxiosProgressEvent } from "axios";
import { UploadFilesystemNodesResponse } from "./types";

export const uploadFilesystemNodes = async (
  files: File[],
  folderUuid?: string,
  onUploadProgress?: (progressEvent: AxiosProgressEvent) => void,
): Promise<UploadFilesystemNodesResponse> => {
  const formData = new FormData();

  // Append multiple files
  files.forEach((file) => {
    formData.append("files[]", file);
  });

  if (folderUuid) {
    formData.append("folderUuid", folderUuid);
  }

  const response = await axios.post<UploadFilesystemNodesResponse>(
    "/api/private/file-manager/upload",
    formData,
    {
      headers: {
        "Content-Type": "multipart/form-data",
      },
      onUploadProgress,
    },
  );

  return response.data;
};
