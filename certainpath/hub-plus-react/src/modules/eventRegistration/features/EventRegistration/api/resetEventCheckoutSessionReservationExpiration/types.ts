export interface ResetEventCheckoutSessionReservationExpirationRequest {
  eventCheckoutSessionUuid: string;
}

export interface ResetEventCheckoutSessionReservationExpirationResponseData {
  id: number;
  uuid: string;
  reservationExpiresAt: string;
}

export interface ResetEventCheckoutSessionReservationExpirationResponse {
  data: ResetEventCheckoutSessionReservationExpirationResponseData;
}
