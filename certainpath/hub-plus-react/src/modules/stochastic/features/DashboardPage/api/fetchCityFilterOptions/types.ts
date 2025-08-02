export interface FetchCityFilterOptionsRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "city";
  sortOrder?: "ASC" | "DESC";
}
export interface CityFilterOption {
  id: number;
  name: string;
}

export interface FetchCityFilterOptionsResponse {
  data: CityFilterOption[];
  meta?: {
    totalCount: number;
  };
}
