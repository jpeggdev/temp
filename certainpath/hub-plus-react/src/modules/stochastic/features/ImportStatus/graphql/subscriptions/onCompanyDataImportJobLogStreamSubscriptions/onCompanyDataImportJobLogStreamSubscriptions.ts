import { gql } from "@apollo/client";

export const ON_COMPANY_DATA_IMPORT_JOB_LOG_STREAM_SUBSCRIPTION = gql`
  subscription OnCompanyDataImportJobLogStream($jobId: Int!) {
    company_data_import_job(where: { id: { _eq: $jobId } }, limit: 1) {
      log_stream
    }
  }
`;
