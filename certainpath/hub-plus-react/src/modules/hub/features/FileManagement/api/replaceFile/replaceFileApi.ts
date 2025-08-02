import axios from "@/api/axiosInstance";
import { AxiosProgressEvent } from "axios";
import { ReplaceFileResponse } from "./types";

export const replaceFile = async (
  fileUuid: string,
  file: File,
  onUploadProgress?: (progressEvent: AxiosProgressEvent) => void,
): Promise<ReplaceFileResponse> => {
  const formData = new FormData();
  formData.append("file", file);

  const response = await axios.post<ReplaceFileResponse>(
    `/api/private/file-manager/files/${fileUuid}/replace`,
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
