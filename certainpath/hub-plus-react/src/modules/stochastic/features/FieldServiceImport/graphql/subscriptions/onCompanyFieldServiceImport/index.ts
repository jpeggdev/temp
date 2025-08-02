import { gql } from "@apollo/client";

export const ON_COMPANY_FIELD_SERVICE_IMPORT_SUBSCRIPTION = gql`
  subscription OnCompanyFieldServiceImport($companyId: Int!) {
    company_field_service_import(
      where: { company_id: { _eq: $companyId }, status: { _neq: "COMPLETED" } }
      order_by: { created_at: desc }
    ) {
      id
      company_id
      is_jobs_or_invoice_file
      is_active_club_member_file
      is_member_file
      trade
      software
      file_path
      status
      progress
      created_at
      updated_at
      uuid
    }
  }
`;
