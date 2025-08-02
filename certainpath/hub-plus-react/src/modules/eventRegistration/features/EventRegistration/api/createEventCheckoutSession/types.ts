export interface CreateEventCheckoutSessionRequest {
  eventSessionUuid: string;
}

export interface CreateEventCheckoutSessionResponseData {
  id: number;
  uuid: string;
  reservationExpiresAt: string;
}

export interface CreateEventCheckoutSessionResponse {
  data: CreateEventCheckoutSessionResponseData;
}
