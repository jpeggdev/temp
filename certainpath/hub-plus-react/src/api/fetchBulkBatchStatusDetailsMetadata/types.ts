export interface FetchBulkBatchStatusDetailsMetadataRequest {
  year?: number;
  week?: number;
}

export interface BulkBatchStatusOption {
  id: string;
  label: string;
  description: string;
  enabled: boolean;
}

export interface BulkBatchStatusDetailsMetadata {
  currentStatus: string;
  bulkBatchStatusOptions: BulkBatchStatusOption[];
}

export interface FetchBulkBatchStatusDetailsMetadataResponse {
  data: BulkBatchStatusDetailsMetadata;
}
