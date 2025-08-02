export interface SetPublishedEventRequest {
  isPublished: boolean;
}

export interface SetPublishedEventData {
  uuid: string;
  isPublished: boolean;
  eventName: string;
}

export interface SetPublishedEventResponse {
  data: SetPublishedEventData;
}
