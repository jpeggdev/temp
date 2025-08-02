export interface FetchEventWaitlistItemsRequest {
  uuid: string;
  searchTerm?: string;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
  page?: number;
  pageSize?: number;
}

export interface EventWaitlistItemResponse {
  id: number;
  firstName: string | null;
  lastName: string | null;
  email: string | null;
  waitlistedAt: string | null;
  companyName: string | null;
  waitlistPosition: number | null;
}

export interface FetchEventWaitlistItemsResponse {
  data: EventWaitlistItemResponse[];
  meta?: {
    totalCount: number;
  };
}
