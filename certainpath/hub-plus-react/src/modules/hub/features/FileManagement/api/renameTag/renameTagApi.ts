import axios from "@/api/axiosInstance";
import { RenameTagRequest, RenameTagResponse } from "./types";

export const renameTag = async (
  id: number,
  data: RenameTagRequest,
): Promise<RenameTagResponse> => {
  const response = await axios.patch<RenameTagResponse>(
    `/api/private/file-management/tags/${id}/rename`,
    data,
  );
  return response.data;
};
