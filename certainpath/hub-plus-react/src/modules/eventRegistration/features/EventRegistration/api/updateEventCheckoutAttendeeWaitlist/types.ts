export interface AttendeeWaitlistRequest {
  attendeeId: number;
  isWaitlist: boolean;
}

export interface UpdateEventCheckoutAttendeeWaitlistRequest {
  attendees: AttendeeWaitlistRequest[];
}

export interface EventCheckoutSessionAttendee {
  id: number;
  firstName: string;
  lastName: string;
  email: string | null;
  specialRequests: string | null;
  isSelected: boolean;
  isWaitlist: boolean;
}

export interface UpdateEventCheckoutAttendeeWaitlistResponseData {
  attendees: EventCheckoutSessionAttendee[];
  success: boolean;
  message: string | null;
}

export interface UpdateEventCheckoutAttendeeWaitlistResponse {
  data: UpdateEventCheckoutAttendeeWaitlistResponseData;
}
