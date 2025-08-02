export interface CompanyDataImportJob {
  id: number;
  company_id: number | null;
  is_jobs_or_invoice_file: boolean;
  is_active_club_member_file: boolean;
  is_member_file: boolean;
  is_prospects_file: boolean;
  progress_percent: number;
  trade: string;
  software: string;
  file_path: string;
  status: string;
  progress: string;
  tag?: string;
  created_at: string;
  updated_at: string;
  uuid: string;
  error_message: string;
}

export interface CompanyDataImportSubscriptionData {
  company_data_import_job: CompanyDataImportJob[];
}
