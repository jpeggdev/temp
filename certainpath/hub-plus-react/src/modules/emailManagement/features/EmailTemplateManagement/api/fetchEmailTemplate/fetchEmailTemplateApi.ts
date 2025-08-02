import { FetchEmailTemplateResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchEmailTemplate = async (
  id: number,
): Promise<FetchEmailTemplateResponse> => {
  const response = await axios.get<FetchEmailTemplateResponse>(
    `/api/private/email-template/${id}`,
  );
  return response.data;
};
