export interface CreateEventTypeRequest {
  name: string;
  description?: string | null;
  isActive?: boolean;
}

export interface CreatedEventTypeData {
  id: number | null;
  name: string | null;
  description: string | null;
  isActive: boolean;
}

export interface CreateEventTypeResponse {
  data: CreatedEventTypeData;
}
