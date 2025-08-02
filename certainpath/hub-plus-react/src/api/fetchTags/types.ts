export interface Tag {
  id: number;
  name: string;
  description?: string;
}

export interface FetchTagsResponse {
  data: {
    tags: Tag[];
  };
  meta?: {
    totalCount: number;
  };
}
