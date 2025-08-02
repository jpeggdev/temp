export interface CreateEventRequest {
  eventCode: string;
  eventName: string;
  eventDescription: string;
  eventPrice: number;
  isPublished?: boolean;
  thumbnailUrl?: string | null;
  thumbnailFileId?: number | null;
  thumbnailFileUuid?: string | null;
  eventCategoryId?: number | null;
  eventTypeId?: number | null;
  fileIds?: number[];
  fileUuids?: string[];
  tagIds?: number[];
  tradeIds?: number[];
  roleIds?: number[];
  isVoucherEligible?: boolean;
}

export interface CreateEventResponseData {
  id: number | null;
  uuid: string | null;
  eventCode: string;
  eventName: string;
  thumbnailUrl?: string | null;
  isPublished: boolean;
  isVoucherEligible?: boolean;
}

export interface CreateEventResponse {
  data: CreateEventResponseData;
}
