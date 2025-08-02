import axiosInstance from "@/api/axiosInstance";
import { ValidateEventCodeRequest, ValidateEventCodeResponse } from "./types";

export const validateEventCode = async (
  requestData: ValidateEventCodeRequest,
): Promise<ValidateEventCodeResponse> => {
  const response = await axiosInstance.post<ValidateEventCodeResponse>(
    "/api/private/event/validate-code",
    requestData,
  );

  return response.data;
};
