export interface UpdateWaitlistPositionRequest {
  uuid: string;
  eventWaitlistId: number;
  newPosition: number;
}

export interface UpdateWaitlistPositionResult {
  message: string;
}

export interface UpdateWaitlistPositionResponse {
  data: UpdateWaitlistPositionResult;
}
