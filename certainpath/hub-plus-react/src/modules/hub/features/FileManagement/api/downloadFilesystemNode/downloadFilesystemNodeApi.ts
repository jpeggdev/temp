import axios from "@/api/axiosInstance";
import { DownloadFilesystemNodeRequest } from "./types";

export const downloadFilesystemNode = async (
  requestData: DownloadFilesystemNodeRequest,
): Promise<Blob> => {
  const response = await axios.get<Blob>(
    `/api/private/file-manager/file/${requestData.fileUuid}/download`,
    {
      responseType: "blob",
    },
  );

  return response.data;
};

// Helper function to trigger file download with proper filename
export const downloadAndSaveFilesystemNode = async (
  fileUuid: string,
  fileName: string,
): Promise<void> => {
  const blob = await downloadFilesystemNode({ fileUuid });

  // Create a URL for the blob
  const url = window.URL.createObjectURL(blob);

  // Create a temporary link element
  const link = document.createElement("a");
  link.href = url;
  link.download = fileName;

  // Append to the document, click it, and clean up
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  // Release the object URL
  window.URL.revokeObjectURL(url);
};
