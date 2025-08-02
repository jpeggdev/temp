export interface CreateEventTagRequest {
  name: string;
}

export interface CreatedEventTagData {
  id: number | null;
  name: string | null;
}

export interface CreateEventTagResponse {
  data: CreatedEventTagData;
}
