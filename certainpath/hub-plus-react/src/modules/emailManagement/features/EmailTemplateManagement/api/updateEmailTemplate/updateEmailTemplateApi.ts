import axios from "../../../../../../api/axiosInstance";
import {
  UpdateEmailTemplateRequest,
  UpdateEmailTemplateResponse,
} from "./types";

export const updateEmailTemplate = async (
  id: number,
  requestData: UpdateEmailTemplateRequest,
): Promise<UpdateEmailTemplateResponse> => {
  const response = await axios.put<UpdateEmailTemplateResponse>(
    `/api/private/email-template/${id}/update`,
    requestData,
  );
  return response.data;
};
