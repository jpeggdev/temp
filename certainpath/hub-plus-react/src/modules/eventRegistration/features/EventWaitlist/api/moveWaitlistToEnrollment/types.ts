export interface MoveWaitlistToEnrollmentRequest {
  uuid: string;
  eventWaitlistId: number;
}

export interface MoveWaitlistToEnrollmentResult {
  enrollmentId: number;
  firstName: string | null;
  lastName: string | null;
  email: string | null;
  companyName: string | null;
  enrolledAt: string | null;
}

export interface MoveWaitlistToEnrollmentResponse {
  data: MoveWaitlistToEnrollmentResult;
}
