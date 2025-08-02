export interface FetchYearFilterOptionsRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "year";
  sortOrder?: "ASC" | "DESC";
}
export interface YearFilterOption {
  id: number;
  name: string;
}

export interface FetchYearFilterOptionsResponse {
  data: YearFilterOption[];
  meta?: {
    totalCount: number;
  };
}
