export interface BulkUpdateBatchStatusRequest {
  year: number;
  week: number;
  status: string;
}

export interface BulkUpdateBatchStatusResponse {
  data: {
    message: string;
  };
}
