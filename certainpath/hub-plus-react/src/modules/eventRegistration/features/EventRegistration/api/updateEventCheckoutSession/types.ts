export interface UpdateAttendeeRequest {
  id?: number;
  firstName: string;
  lastName: string;
  email?: string | null;
  specialRequests?: string | null;
}

export interface UpdateEventCheckoutSessionRequest {
  contactName: string;
  contactEmail: string;
  contactPhone?: string | null;
  groupNotes?: string | null;
  attendees: UpdateAttendeeRequest[];
}

export interface UpdateEventCheckoutSessionResponseData {
  id: number;
  uuid: string;
  contactName: string;
  contactEmail: string;
  contactPhone: string | null;
  groupNotes: string | null;
  attendees: {
    id: number | null;
    firstName: string;
    lastName: string;
    email: string | null;
    specialRequests: string | null;
  }[];
}

export interface UpdateEventCheckoutSessionResponse {
  data: UpdateEventCheckoutSessionResponseData;
}
