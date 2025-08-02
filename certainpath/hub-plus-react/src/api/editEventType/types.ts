export interface EditEventTypeDTO {
  name: string;
  description: string | null;
  isActive: boolean;
}

export interface EditEventTypeResponse {
  data: {
    id: number;
    name: string;
    description: string | null;
    isActive: boolean;
    createdAt: string;
    updatedAt: string;
  };
}
