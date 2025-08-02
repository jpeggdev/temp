import axios from "@/api/axiosInstance";
import { CreateFolderRequest, CreateFolderResponse } from "./types";

export const createFolder = async (
  data: CreateFolderRequest,
): Promise<CreateFolderResponse> => {
  const response = await axios.post<CreateFolderResponse>(
    "/api/private/file-management/folders",
    data,
  );
  return response.data;
};
