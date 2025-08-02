import axios from "../axiosInstance";
import { AxiosProgressEvent } from "axios";
import { UploadProspectSourceResponse } from "./types";

export const uploadProspectSource = async (
  uploadProspectSourceDTO: {
    importType: string;
    file: File;
    software: string;
    tags: string[];
  },
  onUploadProgress?: (progressEvent: AxiosProgressEvent) => void,
): Promise<UploadProspectSourceResponse> => {
  const formData = new FormData();
  formData.append("file", uploadProspectSourceDTO.file);
  formData.append("software", uploadProspectSourceDTO.software);
  formData.append("importType", uploadProspectSourceDTO.importType);
  formData.append("tags", uploadProspectSourceDTO.tags.join(","));

  const response = await axios.post<UploadProspectSourceResponse>(
    `/api/private/stochastic-prospects-source/upload`,
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
