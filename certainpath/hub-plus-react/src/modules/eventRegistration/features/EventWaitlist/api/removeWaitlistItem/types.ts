export interface RemoveWaitlistItemRequest {
  uuid: string;
  eventWaitlistId: number;
}

export interface RemoveWaitlistItemResult {
  message: string;
}

export interface RemoveWaitlistItemResponse {
  data: RemoveWaitlistItemResult;
}
