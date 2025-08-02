import axios from "../axiosInstance";
import { UpdateResourceRequest, UpdateResourceResponse } from "./types";

export const updateResource = async (
  id: number,
  requestData: UpdateResourceRequest,
): Promise<UpdateResourceResponse> => {
  const response = await axios.put<UpdateResourceResponse>(
    `/api/private/resource/${id}/update`,
    requestData,
  );
  return response.data;
};
