export interface FetchEmailCampaignRecipientCountRequest {
  eventSessionId: number;
}

export interface EmailCampaignRecipientCount {
  count: number | null;
}

export interface FetchEmailCampaignRecipientCountResponse {
  data: EmailCampaignRecipientCount;
}
