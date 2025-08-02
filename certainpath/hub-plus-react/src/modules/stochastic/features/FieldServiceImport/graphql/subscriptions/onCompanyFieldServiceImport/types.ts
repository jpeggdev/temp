export interface CompanyFieldServiceImport {
  id: number;
  company_id: number | null;
  is_jobs_or_invoice_file: boolean;
  is_active_club_member_file: boolean;
  is_member_file: boolean;
  trade: string;
  software: string;
  file_path: string;
  status: string;
  progress: string;
  created_at: string;
  updated_at: string;
  uuid: string;
}

export interface CompanyFieldServiceImportSubscriptionData {
  company_field_service_import: CompanyFieldServiceImport[];
}
