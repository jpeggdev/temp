export interface EditEventTypeRequest {
  id: number;
  name: string;
  description?: string | null;
  isActive?: boolean;
}

export interface EditedEventTypeData {
  id: number | null;
  name: string | null;
  description: string | null;
  isActive: boolean;
}

export interface EditEventTypeResponse {
  data: EditedEventTypeData;
}
