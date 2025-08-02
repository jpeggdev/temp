export interface BatchStatus {
  id: number;
  name: string;
}

export interface FetchBatchStatusesResponse {
  data: BatchStatus[];
}
