import { SendTestEmailRequest, SendTestEmailResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const sendTestEmail = async (
  requestData: SendTestEmailRequest,
): Promise<SendTestEmailResponse> => {
  const response = await axios.post<SendTestEmailResponse>(
    "/api/private/email-campaign/send-test-email",
    requestData,
  );
  return response.data;
};
