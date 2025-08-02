export interface PauseCampaignRequest {
  campaignId: number;
}

export interface PauseCampaignResponse {
  data: {
    message: string;
  };
}
