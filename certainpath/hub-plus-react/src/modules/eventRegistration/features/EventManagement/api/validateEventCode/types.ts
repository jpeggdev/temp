export interface ValidateEventCodeRequest {
  eventCode: string;
  eventUuid?: string | null;
}

export interface ValidateEventCodeResponseData {
  codeExists: boolean;
  message?: string | null;
}

export interface ValidateEventCodeResponse {
  data: ValidateEventCodeResponseData;
}
