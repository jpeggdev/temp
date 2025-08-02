import { CopyEmailTemplateResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const copyEmailTemplate = async (
  id: number,
): Promise<CopyEmailTemplateResponse> => {
  const response = await axios.post<CopyEmailTemplateResponse>(
    `/api/private/email-templates/${id}/duplicate`,
  );
  return response.data;
};
