import axios from "../axiosInstance";
import { UploadCampaignFilesResponse } from "./types";
import { AxiosProgressEvent } from "axios";

export const uploadCampaignFile = async (
  campaignId: number,
  file: File,
  onUploadProgress?: (progressEvent: AxiosProgressEvent) => void,
): Promise<UploadCampaignFilesResponse> => {
  const formData = new FormData();
  formData.append("file", file);

  await axios.post<void>(
    `/api/private/campaign/${campaignId}/upload-file`,
    formData,
    {
      headers: {
        "Content-Type": "multipart/form-data",
      },
      onUploadProgress,
    },
  );
};
