import {
  FetchEmailTemplatesRequest,
  FetchEmailTemplatesResponse,
} from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchEmailTemplates = async (
  requestData: FetchEmailTemplatesRequest,
): Promise<FetchEmailTemplatesResponse> => {
  const response = await axios.get<FetchEmailTemplatesResponse>(
    "/api/private/email-templates",
    { params: requestData },
  );
  return response.data;
};
