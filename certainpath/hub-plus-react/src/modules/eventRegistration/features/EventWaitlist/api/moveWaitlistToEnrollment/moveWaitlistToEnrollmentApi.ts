import axiosInstance from "@/api/axiosInstance";
import {
  MoveWaitlistToEnrollmentRequest,
  MoveWaitlistToEnrollmentResponse,
} from "./types";

export const moveWaitlistToEnrollment = async (
  requestData: MoveWaitlistToEnrollmentRequest,
): Promise<MoveWaitlistToEnrollmentResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/waitlist/register`;

  const response = await axiosInstance.post<MoveWaitlistToEnrollmentResponse>(
    url,
    {
      eventWaitlistId: requestData.eventWaitlistId,
    },
  );

  return response.data;
};
