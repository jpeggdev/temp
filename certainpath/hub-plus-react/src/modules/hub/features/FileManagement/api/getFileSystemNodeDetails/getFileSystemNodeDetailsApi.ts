import axios from "@/api/axiosInstance";
import { GetFileSystemNodeDetailsResponse } from "./types";

export const getFileSystemNodeDetails = async (
  uuid: string,
): Promise<GetFileSystemNodeDetailsResponse> => {
  const response = await axios.get<GetFileSystemNodeDetailsResponse>(
    `/api/private/file-management/nodes/${uuid}`,
  );
  return response.data;
};
