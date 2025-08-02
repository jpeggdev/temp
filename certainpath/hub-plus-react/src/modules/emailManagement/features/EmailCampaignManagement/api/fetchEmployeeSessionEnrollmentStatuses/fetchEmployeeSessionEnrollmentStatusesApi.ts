import axiosInstance from "@/api/axiosInstance";
import {
  FetchEmployeeSessionEnrollmentStatusesRequest,
  FetchEmployeeSessionEnrollmentStatusesResponse,
} from "./types";

export const fetchEmployeeSessionEnrollmentStatuses = async (
  requestData: FetchEmployeeSessionEnrollmentStatusesRequest,
): Promise<FetchEmployeeSessionEnrollmentStatusesResponse> => {
  const params = {
    ...requestData,
  };

  const response =
    await axiosInstance.get<FetchEmployeeSessionEnrollmentStatusesResponse>(
      "/api/private/employee-session-enrollment-statuses",
      { params },
    );

  return response.data;
};
