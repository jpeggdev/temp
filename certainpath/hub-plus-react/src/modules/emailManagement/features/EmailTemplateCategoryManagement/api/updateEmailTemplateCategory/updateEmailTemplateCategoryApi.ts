import axios from "../../../../../../api/axiosInstance";
import {
  UpdateEmailTemplateCategoryRequest,
  UpdateEmailTemplateCategoryResponse,
} from "./types";

export const updateEmailTemplateCategory = async (
  id: number,
  requestData: UpdateEmailTemplateCategoryRequest,
): Promise<UpdateEmailTemplateCategoryResponse> => {
  const response = await axios.put<UpdateEmailTemplateCategoryResponse>(
    `/api/private/email-template-category/${id}`,
    requestData,
  );
  return response.data;
};
