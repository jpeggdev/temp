export interface EmailCampaignSendOption {
  id: number;
  label: string;
  value: string;
}

export interface FetchEmailCampaignSendOptionsResponse {
  data: EmailCampaignSendOption[];
  meta?: {
    totalCount: number;
  };
}
