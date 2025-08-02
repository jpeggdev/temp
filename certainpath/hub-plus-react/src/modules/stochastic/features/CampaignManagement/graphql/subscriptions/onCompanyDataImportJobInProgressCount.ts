import { gql } from "@apollo/client";

export const ON_COMPANY_DATA_IMPORT_JOB_IN_PROGRESS_COUNT_SUBSCRIPTION = gql`
  subscription OnCompanyDataImportJobInProgressCount($companyId: Int!) {
    company_data_import_job_aggregate(
      where: {
        company_id: { _eq: $companyId }
        status: { _nin: ["COMPLETED", "FAILED"] }
      }
    ) {
      aggregate {
        count
      }
    }
  }
`;
