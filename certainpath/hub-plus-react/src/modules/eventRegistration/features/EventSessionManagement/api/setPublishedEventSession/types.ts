export interface SetPublishedEventSessionRequest {
  uuid: string;
  isPublished: boolean;
}

export interface SetPublishedEventSessionData {
  uuid: string;
  isPublished: boolean;
  eventId: number;
}

export interface SetPublishedEventSessionResponse {
  data: SetPublishedEventSessionData;
}
