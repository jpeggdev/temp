import { gql } from "@apollo/client";

/**
 * Returns the count of 'members_stream' rows where the 'tenant' equals $tenantId.
 * We use the built-in Hasura "_aggregate" fields to get the row count.
 */
export const ON_MEMBERS_STREAM_COUNT_SUBSCRIPTION = gql`
  subscription OnMembersStreamCount($tenantId: String!) {
    members_stream_aggregate(where: { tenant: { _eq: $tenantId } }) {
      aggregate {
        count
      }
    }
  }
`;
