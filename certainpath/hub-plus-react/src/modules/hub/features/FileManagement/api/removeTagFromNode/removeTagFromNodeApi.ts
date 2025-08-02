import axios from "@/api/axiosInstance";
import { RemoveTagFromNodeRequest, RemoveTagFromNodeResponse } from "./types";

export const removeTagFromNode = async (
  data: RemoveTagFromNodeRequest,
): Promise<RemoveTagFromNodeResponse> => {
  const response = await axios.post<RemoveTagFromNodeResponse>(
    "/api/private/file-management/tags/remove-from-node",
    data,
  );
  return response.data;
};
