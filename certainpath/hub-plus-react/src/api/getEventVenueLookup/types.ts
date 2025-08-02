export interface GetEventVenueLookupRequest {
  isActive?: boolean;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "asc" | "desc";
  searchTerm?: string;
}

export interface ApiEventVenue {
  id: number;
  name: string;
}

export interface GetEventVenueLookupResponse {
  data: ApiEventVenue[];
  meta: {
    totalCount: number;
  };
}
