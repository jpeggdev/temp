import axios from "../../../../../../api/axiosInstance";
import {
  fetchEmailTemplateVariablesRequest,
  fetchEmailTemplateVariablesResponse,
} from "./types";

export const fetchEmailTemplateVariables = async (
  queryParams: fetchEmailTemplateVariablesRequest,
): Promise<fetchEmailTemplateVariablesResponse> => {
  const response = await axios.get<fetchEmailTemplateVariablesResponse>(
    "/api/private/email-template-variables",
    {
      params: queryParams,
    },
  );
  return response.data;
};
