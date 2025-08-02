import axios from "../axiosInstance";

export const downloadCompanyDataImportJobFile = async (
  importJobUuid: string,
) => {
  const response = await axios.get<Blob>(
    `/api/private/download-company-data-import-job-file/${importJobUuid}`,
    { responseType: "blob" },
  );

  return {
    blob: response.data,
    headers: response.headers,
  };
};
