export interface GetResourceTagsRequest {
  name?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

export interface GetResourceTagsResponse {
  data: {
    tags: {
      id: number;
      name: string;
    }[];
  };
  meta?: {
    totalCount: number;
  };
}
