import axios from "../axiosInstance";

export const fetchBatchProspectsCsv = async (
  batchId: number,
): Promise<Blob> => {
  const response = await axios.get<Blob>(
    `/api/private/batch/${batchId}/prospects/csv`,
    {
      responseType: "blob",
      headers: {
        Accept: "text/csv",
      },
    },
  );

  return response.data;
};
