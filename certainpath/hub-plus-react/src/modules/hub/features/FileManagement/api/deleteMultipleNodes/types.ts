export interface BulkDeleteNodesRequest {
  uuids: string[];
}

export interface BulkDeleteNodesData {
  jobId: string;
  status: string; // 'pending', 'processing', 'completed'
  totalFiles: number;
  success: boolean;
}

export interface BulkDeleteNodesResponse {
  data: BulkDeleteNodesData;
}
