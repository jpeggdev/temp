export interface FetchSingleEventTagRequest {
  id: number;
}

export interface SingleEventTagData {
  id: number | null;
  name: string | null;
}

export interface FetchSingleEventTagResponse {
  data: SingleEventTagData;
}
