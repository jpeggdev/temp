export interface DuplicateEventResponseData {
  id: number | null;
  uuid: string | null;
  eventCode: string;
  eventName: string;
  thumbnailUrl?: string | null;
  isPublished: boolean;
  isVoucherEligible?: boolean;
}

export interface DuplicateEventResponse {
  data: DuplicateEventResponseData;
}
