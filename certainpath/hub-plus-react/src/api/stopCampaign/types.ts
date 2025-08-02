export interface StopCampaignRequest {
  campaignId: number;
}

export interface StopCampaignResponse {
  data: {
    message: string;
  };
}
