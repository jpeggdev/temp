import axios from "../../../../../../api/axiosInstance";
import {
  CreateEmailTemplateRequest,
  CreateEmailTemplateResponse,
} from "./types";

export const createEmailTemplate = async (
  requestData: CreateEmailTemplateRequest,
): Promise<CreateEmailTemplateResponse> => {
  const response = await axios.post<CreateEmailTemplateResponse>(
    "/api/private/email-template/create",
    requestData,
  );
  return response.data;
};
