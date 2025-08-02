import { DateTimeImmutable } from "@/utils/dateUtils";

export interface FetchEventSessionsLookupRequest {
  eventId?: number;
  isPublished?: boolean;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "asc" | "desc";
  searchTerm?: string;
}

export interface EventSessionLookup {
  id: number;
  uuid: string;
  eventId: number;
  startDate: string | DateTimeImmutable | null;
}

export interface FetchEventSessionsLookupResponse {
  data: EventSessionLookup[];
  meta?: {
    totalCount: number;
  };
}
