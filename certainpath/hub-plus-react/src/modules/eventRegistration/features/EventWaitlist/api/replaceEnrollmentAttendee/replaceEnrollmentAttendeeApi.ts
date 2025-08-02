import axiosInstance from "@/api/axiosInstance";
import {
  ReplaceEnrollmentAttendeeRequest,
  ReplaceEnrollmentAttendeeResponse,
} from "./types";

export const replaceEnrollmentAttendee = async (
  requestData: ReplaceEnrollmentAttendeeRequest,
): Promise<ReplaceEnrollmentAttendeeResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/enrollments/replace-attendee`;

  const response = await axiosInstance.post<ReplaceEnrollmentAttendeeResponse>(
    url,
    {
      eventEnrollmentId: requestData.eventEnrollmentId,
      newFirstName: requestData.newFirstName,
      newLastName: requestData.newLastName,
      newEmail: requestData.newEmail,
    },
  );

  return response.data;
};
