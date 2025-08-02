import axios from "@/api/axiosInstance";
import { RenameNodeRequest, RenameNodeResponse } from "./types";

export const renameNode = async (
  uuid: string,
  data: RenameNodeRequest,
): Promise<RenameNodeResponse> => {
  const response = await axios.patch<RenameNodeResponse>(
    `/api/private/file-management/nodes/${uuid}/rename`,
    data,
  );
  return response.data;
};
