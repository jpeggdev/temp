import axios from "@/api/axiosInstance";
import { AssignTagToNodeRequest, AssignTagToNodeResponse } from "./types";

export const assignTagToNode = async (
  data: AssignTagToNodeRequest,
): Promise<AssignTagToNodeResponse> => {
  const response = await axios.post<AssignTagToNodeResponse>(
    "/api/private/file-management/tags/assign-to-node",
    data,
  );
  return response.data;
};
