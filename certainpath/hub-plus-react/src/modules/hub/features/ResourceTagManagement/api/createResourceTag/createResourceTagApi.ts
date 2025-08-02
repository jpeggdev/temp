import axios from "../../../../../../api/axiosInstance";
import { CreateResourceTagRequest, CreateResourceTagResponse } from "./types";

export const createResourceTag = async (
  requestData: CreateResourceTagRequest,
): Promise<CreateResourceTagResponse> => {
  const response = await axios.post<CreateResourceTagResponse>(
    "/api/private/resource/tag/create",
    requestData,
  );
  return response.data;
};
