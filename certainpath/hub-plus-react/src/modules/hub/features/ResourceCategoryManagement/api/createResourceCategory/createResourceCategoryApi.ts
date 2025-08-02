import axios from "../../../../../../api/axiosInstance";
import {
  CreateResourceCategoryRequest,
  CreateResourceCategoryResponse,
} from "./types";

export const createResourceCategory = async (
  requestData: CreateResourceCategoryRequest,
): Promise<CreateResourceCategoryResponse> => {
  const response = await axios.post<CreateResourceCategoryResponse>(
    "/api/private/resource/category/create",
    requestData,
  );
  return response.data;
};
