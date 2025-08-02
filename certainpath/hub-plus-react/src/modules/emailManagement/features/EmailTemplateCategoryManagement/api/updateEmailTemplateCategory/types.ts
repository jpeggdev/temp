export interface UpdateEmailTemplateCategoryRequest {
  name: string;
  displayedName: string;
  description: string | null;
  colorId: number;
}

export interface UpdateEmailTemplateCategoryResponse {
  data: {
    id: number | null;
    name: string | null;
    displayedName: string | null;
    description: string | null;
    colorId: number | null;
    colorValue: string | null;
  };
}
