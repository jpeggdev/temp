import axios from "../../../../../../api/axiosInstance";
import {
  CreateEmailTemplateCategoryRequest,
  CreateEmailTemplateCategoryResponse,
} from "./types";

export const createEmailTemplateCategory = async (
  requestData: CreateEmailTemplateCategoryRequest,
): Promise<CreateEmailTemplateCategoryResponse> => {
  const response = await axios.post<CreateEmailTemplateCategoryResponse>(
    "/api/private/email-template-category/create",
    requestData,
  );
  return response.data;
};
