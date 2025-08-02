export interface FetchEventSearchResultsRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "asc" | "desc";
  tradeIds?: number[];
  categoryIds?: number[];
  employeeRoleIds?: number[];
  tagIds?: number[];
  eventTypeIds?: number[];
  showFavorites?: boolean;
  onlyPastEvents?: boolean;
  startDate?: string;
  endDate?: string;
}

export interface SearchResultEvent {
  id: number;
  uuid: string;
  eventCode: string;
  eventName: string;
  eventDescription: string;
  eventPrice: number;
  isPublished: boolean;
  thumbnailUrl: string | null;
  eventTypeName: string | null;
  eventCategoryName: string | null;
  createdAt: string | null;
  viewCount: number | null;
  isVoucherEligible: boolean;
}

export interface EventSearchFacet {
  id: number;
  name: string;
  eventCount: number;
  icon?: string;
}

export interface EventSearchFilters {
  eventTypes: EventSearchFacet[];
  categories: EventSearchFacet[];
  trades: EventSearchFacet[];
  employeeRoles: EventSearchFacet[];
}

export interface SearchResultsData {
  events: SearchResultEvent[];
  filters: EventSearchFilters;
}

export interface FetchEventSearchResultsResponse {
  data: SearchResultsData;
  meta?: {
    totalCount?: number;
  };
}
