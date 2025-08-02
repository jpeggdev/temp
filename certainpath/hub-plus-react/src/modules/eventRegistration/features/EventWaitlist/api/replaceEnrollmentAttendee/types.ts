export interface ReplaceEnrollmentAttendeeRequest {
  uuid: string;
  eventEnrollmentId: number;
  newFirstName: string;
  newLastName: string;
  newEmail: string;
}

export interface ReplaceEnrollmentAttendeeResult {
  enrollmentId: number;
  firstName: string | null;
  lastName: string | null;
  email: string | null;
  employeeId: number | null;
}

export interface ReplaceEnrollmentAttendeeResponse {
  data: ReplaceEnrollmentAttendeeResult;
}
