export interface FetchEventTypesRequest {
  name?: string;
  isActive?: boolean;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

export interface EventTypeItem {
  id: number;
  name: string;
  description: string | null;
  isActive: boolean;
}

export interface EventTypesPayload {
  eventTypes: EventTypeItem[];
  totalCount: number;
}

export interface FetchEventTypesResponse {
  data: EventTypesPayload;
  meta?: {
    totalCount?: number;
  };
}
