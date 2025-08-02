export interface FetchEventsLookupRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "asc" | "desc";
}

export interface EventLookup {
  id: number;
  name: string;
}

export interface FetchEventsLookupResponse {
  data: EventLookup[];
  meta?: {
    totalCount: number;
  };
}
