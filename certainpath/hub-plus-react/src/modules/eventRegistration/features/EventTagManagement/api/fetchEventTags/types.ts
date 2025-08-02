export interface FetchEventTagsRequest {
  name?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

export interface EventTagItem {
  id: number;
  name: string;
}

export interface EventTagsPayload {
  tags: EventTagItem[];
  totalCount: number;
}

export interface FetchEventTagsResponse {
  data: EventTagsPayload;
  meta?: {
    totalCount?: number;
  };
}
