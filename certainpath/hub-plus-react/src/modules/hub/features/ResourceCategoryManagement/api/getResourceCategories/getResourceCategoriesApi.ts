import axios from "../../../../../../api/axiosInstance";
import {
  GetResourceCategoriesRequest,
  GetResourceCategoriesResponse,
} from "./types";

export const getResourceCategories = async (
  queryParams: GetResourceCategoriesRequest,
): Promise<GetResourceCategoriesResponse> => {
  const response = await axios.get<GetResourceCategoriesResponse>(
    "/api/private/resource/categories",
    {
      params: queryParams,
    },
  );
  return response.data;
};
