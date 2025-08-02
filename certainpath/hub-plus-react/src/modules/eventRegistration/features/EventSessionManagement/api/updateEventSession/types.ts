export interface UpdateEventSessionRequest {
  eventUuid: string;
  startDate?: string;
  endDate?: string;
  maxEnrollments?: number;
  virtualLink?: string | null;
  notes?: string | null;
  isPublished?: boolean;
  name?: string;
  instructorId?: number | null;
  isVirtualOnly?: boolean;
  venueId?: number | null;
  timezoneId?: number | null;
}

export interface UpdatedEventSessionData {
  id: number;
  uuid: string;
  eventId: number;
  startDate: string | null;
  endDate: string | null;
  maxEnrollments: number;
  virtualLink: string | null;
  notes: string | null;
  isPublished: boolean;
  createdAt: string | null;
  updatedAt: string | null;
  isVirtualOnly: boolean;
  venueId: number | null;
  venueName: string | null;
  timezoneId: number | null;
  timezoneName: string | null;
}

export interface UpdateEventSessionResponse {
  data: UpdatedEventSessionData;
}
