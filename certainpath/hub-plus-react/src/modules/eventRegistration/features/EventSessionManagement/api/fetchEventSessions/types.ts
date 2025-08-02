export interface FetchEventSessionsRequest {
  eventUuid?: string;
  isPublished?: boolean;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "asc" | "desc";
}

export interface SessionSummary {
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
  timezoneIdentifier: string | null;
  timezoneShortName: string | null;
}

interface FetchEventSessionsResponseData {
  eventName: string | null;
  sessions: SessionSummary[];
}

export interface FetchEventSessionsResponse {
  data: FetchEventSessionsResponseData;
  meta?: {
    totalCount?: number;
  };
}
