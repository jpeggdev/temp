export interface CreateEmailTemplateCategoryRequest {
  name: string;
  description?: string | null;
  colorId: number;
}

export interface Color {
  id: number;
  value: string;
}

export interface CreateEmailTemplateCategoryResponse {
  data: {
    id: number | null;
    name: string | null;
    displayedName: string | null;
    color: Color;
  };
}
