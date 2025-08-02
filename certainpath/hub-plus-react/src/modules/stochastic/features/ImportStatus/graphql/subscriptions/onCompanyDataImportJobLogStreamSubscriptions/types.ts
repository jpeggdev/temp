export interface CompanyDataImportJobLogStream {
  log_stream: string | null;
}

export interface CompanyDataImportLogStreamSubscriptionData {
  company_data_import_job: CompanyDataImportJobLogStream[];
}
