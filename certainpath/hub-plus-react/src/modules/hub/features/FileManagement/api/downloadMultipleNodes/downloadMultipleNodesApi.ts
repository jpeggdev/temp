import axios from "@/api/axiosInstance";
import { BulkDownloadNodesRequest } from "./types";

export const downloadMultipleNodes = async (
  requestData: BulkDownloadNodesRequest,
): Promise<Blob> => {
  const response = await axios.post<Blob>(
    `/api/private/file-manager/nodes/download`,
    requestData,
    {
      responseType: "blob",
    },
  );

  return response.data;
};

// Helper function to trigger download of multiple files as a zip
export const downloadAndSaveMultipleNodes = async (
  uuids: string[],
  defaultFileName = "files.zip",
): Promise<void> => {
  try {
    const requestData: BulkDownloadNodesRequest = { uuids };
    const response = await axios.post<Blob>(
      `/api/private/file-manager/nodes/download`,
      requestData,
      {
        responseType: "blob",
      },
    );

    const blob = response.data;

    // Extract filename from Content-Disposition header if available
    let fileName = defaultFileName;
    const contentDisposition = response.headers["content-disposition"];
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+)"/);
      if (filenameMatch && filenameMatch[1]) {
        fileName = filenameMatch[1];
      }
    }

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
  } catch (error) {
    console.error("Error downloading files:", error);
    throw error;
  }
};
