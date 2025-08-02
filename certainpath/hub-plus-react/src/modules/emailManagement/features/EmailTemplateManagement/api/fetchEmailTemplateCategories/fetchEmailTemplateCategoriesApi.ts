import axios from "../../../../../../api/axiosInstance";
import {
  fetchEmailTemplateCategoriesRequest,
  fetchEmailTemplateCategoriesResponse,
} from "./types";

export const fetchEmailTemplateCategories = async (
  queryParams: fetchEmailTemplateCategoriesRequest,
): Promise<fetchEmailTemplateCategoriesResponse> => {
  const response = await axios.get<fetchEmailTemplateCategoriesResponse>(
    "/api/private/email-template-categories",
    {
      params: queryParams,
    },
  );
  return response.data;
};
