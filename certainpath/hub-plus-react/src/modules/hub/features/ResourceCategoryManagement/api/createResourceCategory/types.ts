export interface CreateResourceCategoryRequest {
  name: string;
}

export interface CreateResourceCategoryResponse {
  data: {
    id: number | null;
    name: string | null;
  };
}
