import { gql } from "@apollo/client";

export const ON_COMPANY_DATA_IMPORT_JOB_SUBSCRIPTION = gql`
  subscription OnCompanyDataImportJob(
    $companyId: Int!
    $limit: Int!
    $offset: Int!
  ) {
    company_data_import_job(
      where: { company_id: { _eq: $companyId } }
      order_by: { created_at: desc }
      limit: $limit
      offset: $offset
    ) {
      id
      company_id
      is_jobs_or_invoice_file
      is_active_club_member_file
      is_member_file
      is_prospects_file
      progress_percent
      trade
      software
      file_path
      status
      progress
      tag
      created_at
      updated_at
      uuid
      error_message
    }
  }
`;
