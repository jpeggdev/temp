import axios from "@/api/axiosInstance";
import { GetPresignedUrlsRequest, GetPresignedUrlsResponse } from "./types";

export const getPresignedUrls = async (
  request: GetPresignedUrlsRequest,
): Promise<GetPresignedUrlsResponse> => {
  const response = await axios.post<GetPresignedUrlsResponse>(
    "/api/private/file-management/files/presigned-urls",
    request,
  );
  return response.data;
};
