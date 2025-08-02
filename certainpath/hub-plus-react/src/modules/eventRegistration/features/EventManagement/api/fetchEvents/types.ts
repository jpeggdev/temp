export interface FetchEventsRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "desc" | "asc";
  tradeIds?: number[];
  categoryIds?: number[];
  employeeRoleIds?: number[];
  tagIds?: number[];
  eventTypeIds?: number[];
}

export interface EventSummary {
  id: number;
  uuid: string;
  eventCode: string;
  eventName: string;
  eventDescription: string;
  isPublished: boolean;
  eventPrice: number;
  thumbnailUrl: string | null;
  eventTypeName: string | null;
  eventCategoryName: string | null;
  createdAt: string | null;
}

export interface FetchEventsResponse {
  data: EventSummary[];
  meta?: {
    totalCount?: number;
  };
}
