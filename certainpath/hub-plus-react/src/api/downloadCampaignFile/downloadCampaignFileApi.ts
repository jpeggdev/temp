import axios from "../axiosInstance";
import { DownloadCampaignFileRequest } from "./types";

export const downloadCampaignFile = async (
  requestData: DownloadCampaignFileRequest,
): Promise<Blob> => {
  const response = await axios.get<Blob>(
    `/api/private/campaign/file/download`,
    {
      params: {
        bucketName: requestData.bucketName,
        objectKey: requestData.objectKey,
      },
      responseType: "blob",
    },
  );

  return response.data;
};
