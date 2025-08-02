export interface FetchWaitlistDetailsRequest {
  uuid: string;
}

export interface WaitlistDetails {
  id: number;
  uuid: string;
  name: string | null;
  startDate: string | null;
  endDate: string | null;
  timezoneShortName: string | null;
  timezoneIdentifier: string | null;
  waitlistCount: number;
  enrolledCount: number;
  checkoutReservedCount: number;
  availableSeatCount: number;
  maxEnrollments: number;
}

export interface FetchWaitlistDetailsResponse {
  data: WaitlistDetails;
}
