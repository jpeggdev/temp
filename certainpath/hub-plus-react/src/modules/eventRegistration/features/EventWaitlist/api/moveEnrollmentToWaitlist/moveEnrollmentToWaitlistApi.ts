import axiosInstance from "@/api/axiosInstance";
import {
  MoveEnrollmentToWaitlistRequest,
  MoveEnrollmentToWaitlistResponse,
} from "./types";

export const moveEnrollmentToWaitlist = async (
  requestData: MoveEnrollmentToWaitlistRequest,
): Promise<MoveEnrollmentToWaitlistResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/waitlist/from-enrollment`;

  const response = await axiosInstance.post<MoveEnrollmentToWaitlistResponse>(
    url,
    {
      enrollmentId: requestData.enrollmentId,
    },
  );

  return response.data;
};
