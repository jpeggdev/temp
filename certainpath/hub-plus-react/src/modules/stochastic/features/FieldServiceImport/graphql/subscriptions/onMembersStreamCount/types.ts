/**
 * The shape of the 'members_stream_aggregate' response:
 */
export interface MembersStreamAggregate {
  aggregate: {
    count: number;
  };
}

/**
 * The data shape returned by our OnMembersStreamCount subscription.
 */
export interface MembersStreamCountSubscriptionData {
  members_stream_aggregate: MembersStreamAggregate;
}
