import axios from "../../../../../../api/axiosInstance";
import { GetEditResourceCategoryResponse } from "./types";

export const getEditResourceCategory = async (
  id: number,
): Promise<GetEditResourceCategoryResponse> => {
  const response = await axios.get<GetEditResourceCategoryResponse>(
    `/api/private/resource/category/${id}`,
  );
  return response.data;
};
