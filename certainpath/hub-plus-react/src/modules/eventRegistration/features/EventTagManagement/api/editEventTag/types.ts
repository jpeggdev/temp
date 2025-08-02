export interface EditEventTagRequest {
  id: number;
  name: string;
}

export interface EditedEventTagData {
  id: number | null;
  name: string | null;
}

export interface EditEventTagResponse {
  data: EditedEventTagData;
}
