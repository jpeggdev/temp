export interface GetResourceCategoriesRequest {
  name?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

export interface GetResourceCategoriesResponse {
  data: {
    categories: {
      id: number;
      name: string;
    }[];
  };
  meta?: {
    totalCount: number;
  };
}
