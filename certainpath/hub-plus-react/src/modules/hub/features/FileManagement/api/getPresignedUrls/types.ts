export interface GetPresignedUrlsRequest {
  fileUuids: string[];
}

export interface PresignedUrlsMap {
  [fileUuid: string]: string | null;
}

export interface GetPresignedUrlsData {
  presignedUrls: PresignedUrlsMap;
}

export interface GetPresignedUrlsResponse {
  data: GetPresignedUrlsData;
  meta?: {
    totalCount?: number;
    hasMore?: boolean;
  };
}
