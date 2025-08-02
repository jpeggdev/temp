export interface SingleEventSession {
  id: number;
  uuid: string;
  eventId: number;
  startDate: string | null;
  endDate: string | null;
  maxEnrollments: number;
  virtualLink: string | null;
  notes: string | null;
  isPublished: boolean;
  name: string;
  createdAt: string | null;
  instructorId: number | null;
  instructorName: string | null;
  isVirtualOnly: boolean;
  venueId: number | null;
  venueName: string | null;
  timezoneId: number | null;
  timezoneName: string | null;
}

export interface FetchSingleEventSessionResponse {
  data: SingleEventSession;
}
