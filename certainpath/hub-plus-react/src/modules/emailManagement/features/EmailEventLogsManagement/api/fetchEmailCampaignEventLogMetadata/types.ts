export type emailEventPeriodFilter =
  | "today"
  | "last_7_days"
  | "last_30_days"
  | "last_90_days";

export interface FetchEmailCampaignEventLogsMetadataRequest {
  emailEventPeriodFilter: emailEventPeriodFilter;
}

export interface EmailEventCount {
  delivered: number;
  opened: number;
  clicked: number;
  failed: number;
}

export interface EmailEventRate {
  delivered: number;
  opened: number;
  clicked: number;
  failed: number;
}

export interface EmailCampaignEventLogsMetadata {
  emailEventCount: EmailEventCount;
  emailEventRate: EmailEventRate;
}
export interface FetchEmailCampaignEventLogsMetadataResponse {
  data: EmailCampaignEventLogsMetadata;
}
