export interface MoveEnrollmentToWaitlistRequest {
  uuid: string;
  enrollmentId: number;
}

export interface MoveEnrollmentToWaitlistResult {
  waitlistId: number;
  firstName: string | null;
  lastName: string | null;
  email: string | null;
  waitlistedAt: string | null;
}

export interface MoveEnrollmentToWaitlistResponse {
  data: MoveEnrollmentToWaitlistResult;
}
