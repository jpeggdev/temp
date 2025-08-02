export interface ReplaceEnrollmentWithEmployeeRequest {
  uuid: string;
  eventEnrollmentId: number;
  employeeId: number;
}

export interface ReplaceEnrollmentWithEmployeeResult {
  enrollmentId: number;
  firstName: string | null;
  lastName: string | null;
  email: string | null;
  employeeId: number | null;
}

export interface ReplaceEnrollmentWithEmployeeResponse {
  data: ReplaceEnrollmentWithEmployeeResult;
}
