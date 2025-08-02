export interface EditEventCategoryDTO {
  name: string;
  description: string | null;
  isActive: boolean;
}

export interface EditEventCategoryResponse {
  data: {
    id: number;
    name: string;
    description: string | null;
    isActive: boolean;
    createdAt: string;
    updatedAt: string;
  };
}
