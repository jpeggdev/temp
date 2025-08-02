import axios from "../../../../../../api/axiosInstance";
import { DeleteResourceCategoryResponse } from "./types";

export const deleteResourceCategory = async (
  categoryId: number,
): Promise<DeleteResourceCategoryResponse> => {
  const response = await axios.delete<DeleteResourceCategoryResponse>(
    `/api/private/resource/category/${categoryId}/delete`,
  );
  return response.data;
};
