export interface FetchTimezonesRequest {
  page?: number;
  pageSize?: number;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
  searchTerm?: string;
}

export interface Timezone {
  id: number;
  name: string;
  shortName: string;
}

export interface FetchTimezonesResponse {
  data: Timezone[];
  meta: {
    totalCount: number;
  };
}
