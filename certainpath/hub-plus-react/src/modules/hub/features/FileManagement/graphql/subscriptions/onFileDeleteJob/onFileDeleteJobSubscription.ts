import { gql } from "@apollo/client";

export const ON_FILE_DELETE_JOB_SUBSCRIPTION = gql`
  subscription OnFileDeleteJob($uuid: uuid!) {
    file_delete_job(where: { uuid: { _eq: $uuid } }, limit: 1) {
      id
      uuid
      status
      progress_percent
      total_files
      processed_files
      successful_deletes
      failed_items
      created_at
      updated_at
    }
  }
`;
