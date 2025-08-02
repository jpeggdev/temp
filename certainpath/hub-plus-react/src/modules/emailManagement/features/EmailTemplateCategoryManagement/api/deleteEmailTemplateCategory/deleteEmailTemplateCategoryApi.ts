import axios from "../../../../../../api/axiosInstance";
import { DeleteEmailTemplateCategoryResponse } from "./types";

export const deleteEmailTemplateCategory = async (
  id: number,
): Promise<DeleteEmailTemplateCategoryResponse> => {
  const response = await axios.delete<DeleteEmailTemplateCategoryResponse>(
    `/api/private/email-template-category/${id}/delete`,
  );
  return response.data;
};
