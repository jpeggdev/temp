export interface FetchSingleEventTypeRequest {
  id: number;
}

export interface SingleEventTypeData {
  id: number | null;
  name: string;
  description: string | null;
  isActive: boolean;
}

export interface FetchSingleEventTypeResponse {
  data: SingleEventTypeData;
}
