export interface CompanyDataImportJobInProgressCountAggregate {
  aggregate: {
    count: number;
  };
}

export interface CompanyDataImportJobInProgressCountSubscriptionData {
  company_data_import_job_aggregate: CompanyDataImportJobInProgressCountAggregate;
}
