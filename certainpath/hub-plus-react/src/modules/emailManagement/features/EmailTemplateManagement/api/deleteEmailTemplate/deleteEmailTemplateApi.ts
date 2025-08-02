import { DeleteEmailTemplateResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const deleteEmailTemplate = async (
  id: number,
): Promise<DeleteEmailTemplateResponse> => {
  const response = await axios.delete<DeleteEmailTemplateResponse>(
    `/api/private/email-templates/${id}`,
  );
  return response.data;
};
