import axios from "../axiosInstance";
import { CreateResourceRequest, CreateResourceResponse } from "./types";

export const createResource = async (
  requestData: CreateResourceRequest,
): Promise<CreateResourceResponse> => {
  const response = await axios.post<CreateResourceResponse>(
    "/api/private/resource/create",
    requestData,
  );
  return response.data;
};
