import axios from "@/api/axiosInstance";
import { DeleteNodeResponse } from "./types";

export const deleteNode = async (uuid: string): Promise<DeleteNodeResponse> => {
  const response = await axios.delete<DeleteNodeResponse>(
    `/api/private/file-management/nodes/${uuid}`,
  );
  return response.data;
};
