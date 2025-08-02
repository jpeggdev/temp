export interface CampaignStatus {
  id: number;
  name: string;
}

export interface FetchCampaignStatusesResponse {
  data: CampaignStatus[];
}
