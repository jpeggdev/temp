export interface EditResourceCategoryRequest {
  name: string;
}

export interface EditResourceCategoryResponse {
  data: {
    id: number | null;
    name: string | null;
  };
}
