import axios from "../../../../../../api/axiosInstance";
import { GetEditEmailTemplateCategoryResponse } from "./types";

export const getEditEmailTemplateCategory = async (
  id: number,
): Promise<GetEditEmailTemplateCategoryResponse> => {
  const response = await axios.get<GetEditEmailTemplateCategoryResponse>(
    `/api/private/email-template-category/${id}`,
  );
  return response.data;
};
