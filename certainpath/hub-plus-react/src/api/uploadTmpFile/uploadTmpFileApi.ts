import axios from "../axiosInstance";
import { AxiosProgressEvent } from "axios";

export interface UploadTmpFileApiResponse {
  data: {
    fileUrl: string;
    fileId: number;
    name: string;
    fileUuid: string;
  };
}

export const uploadTmpFile = async (
  file: File,
  bucketName?: string,
  folderName?: string,
  onUploadProgress?: (progressEvent: AxiosProgressEvent) => void,
): Promise<UploadTmpFileApiResponse> => {
  const formData = new FormData();
  formData.append("file", file);

  if (bucketName) {
    formData.append("bucketName", bucketName);
  }

  if (folderName) {
    formData.append("folderName", folderName);
  }

  const response = await axios.post<UploadTmpFileApiResponse>(
    "/api/private/tmp/file-upload",
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
