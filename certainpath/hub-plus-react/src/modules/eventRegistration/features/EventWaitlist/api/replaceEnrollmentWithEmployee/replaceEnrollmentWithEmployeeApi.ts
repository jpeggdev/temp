import axiosInstance from "@/api/axiosInstance";
import {
  ReplaceEnrollmentWithEmployeeRequest,
  ReplaceEnrollmentWithEmployeeResponse,
} from "./types";

export const replaceEnrollmentWithEmployee = async (
  requestData: ReplaceEnrollmentWithEmployeeRequest,
): Promise<ReplaceEnrollmentWithEmployeeResponse> => {
  const url = `/api/private/event-sessions/${requestData.uuid}/enrollments/replace-with-employee`;

  const response =
    await axiosInstance.post<ReplaceEnrollmentWithEmployeeResponse>(url, {
      eventEnrollmentId: requestData.eventEnrollmentId,
      employeeId: requestData.employeeId,
    });

  return response.data;
};
