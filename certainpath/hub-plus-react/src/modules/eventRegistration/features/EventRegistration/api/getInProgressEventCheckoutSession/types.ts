export interface GetInProgressEventCheckoutSessionResponseData {
  id: number | null;
  uuid: string | null;
  createdAt: string | null;
  eventName: string | null;
  eventSessionName: string | null;
  startDate: string | null;
  endDate: string | null;
}

export interface GetInProgressEventCheckoutSessionResponse {
  data: GetInProgressEventCheckoutSessionResponseData;
}
