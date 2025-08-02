export interface CreateEventCategoryRequest {
  name: string;
  description: string | null;
  isActive: boolean;
}

export interface CreateEventCategoryResponse {
  data: {
    eventCategory: {
      id: number;
      name: string;
      description: string | null;
      isActive: boolean;
    };
  };
}
