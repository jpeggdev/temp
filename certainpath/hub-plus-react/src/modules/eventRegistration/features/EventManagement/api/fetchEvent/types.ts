export interface FetchEventRequest {
  uuid: string;
}

export interface SingleEventData {
  id: number | null;
  uuid: string | null;
  eventCode: string;
  eventName: string;
  eventDescription: string;
  eventPrice: number;
  isPublished: boolean;
  isVoucherEligible?: boolean;
  eventTypeId: number | null;
  eventTypeName: string | null;
  eventCategoryId: number | null;
  eventCategoryName: string | null;
  thumbnailFileUuid: string | null;
  tags: Array<{ id: number; name: string }>;
  trades: Array<{ id: number; name: string }>;
  roles: Array<{ id: number; name: string }>;
  files: Array<{
    id: number;
    uuid: string;
    originalFileName: string;
    fileUrl: string | null;
  }>;
}

export interface FetchEventResponse {
  data: SingleEventData;
}
