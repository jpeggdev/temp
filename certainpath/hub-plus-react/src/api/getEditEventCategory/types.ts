export interface GetEditEventCategoryResponse {
  data: {
    id: number | null;
    name: string;
    description: string | null;
    isActive: boolean;
  };
}
