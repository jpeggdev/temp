export interface FileDeleteJob {
  id: number;
  uuid: string;
  status: string; // 'pending', 'processing', 'completed'
  progress_percent: string;
  total_files: number;
  processed_files: number;
  successful_deletes: number;
  failed_items: Record<string, string> | null;
  created_at: string;
  updated_at: string;
}

export interface FileDeleteJobSubscriptionData {
  file_delete_job: FileDeleteJob[];
}
