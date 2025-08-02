export interface FetchEventCategoriesRequest {
  searchTerm?: string;
  page?: number;
  sortBy?: string;
  sortOrder?: "ASC" | "DESC";
  pageSize?: number;
  name?: string;
  description?: string;
  isActive?: boolean;
}

export interface FetchEventCategoriesResponse {
  data: ApiEventCategory[];
  meta: {
    totalCount: number;
  };
}

export interface ApiEventCategory {
  id: number;
  name: string;
  description: string | null;
  isActive: boolean;
  createdAt?: string;
  updatedAt?: string;
}
