import axios from "../../../../../../api/axiosInstance";
import {
  EditResourceCategoryRequest,
  EditResourceCategoryResponse,
} from "./types";

export const editResourceCategory = async (
  categoryId: number,
  requestData: EditResourceCategoryRequest,
): Promise<EditResourceCategoryResponse> => {
  const response = await axios.put<EditResourceCategoryResponse>(
    `/api/private/resource/category/${categoryId}/edit`,
    requestData,
  );
  return response.data;
};
