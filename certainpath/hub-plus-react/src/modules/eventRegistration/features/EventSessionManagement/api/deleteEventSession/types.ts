export interface DeleteEventSessionRequest {
  uuid: string;
}

export interface DeletedEventSessionData {
  id: number;
  message: string;
}

export interface DeleteEventSessionResponse {
  data: DeletedEventSessionData;
}
