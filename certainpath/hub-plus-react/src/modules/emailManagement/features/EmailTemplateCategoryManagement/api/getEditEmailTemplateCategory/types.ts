export interface GetEditEmailTemplateCategoryResponse {
  data: {
    id: number | null;
    name: string | null;
    displayedName: string | null;
    description: string | null;
    colorId: number | null;
    colorValue: string | null;
    availableColors: {
      id: number;
      value: string;
    }[];
  };
}
