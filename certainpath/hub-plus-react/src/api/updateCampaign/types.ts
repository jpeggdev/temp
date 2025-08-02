export interface UpdateCampaignRequest {
  name?: string;
  description?: string;
  phoneNumber?: string;
  status?: number;
}

export interface CampaignStatus {
  id: number | null;
  name: string | null;
}

export interface Campaign {
  id: number;
  companyId: number;
  name: string;
  description?: string | null;
  startDate: string;
  endDate: string;
  mailingFrequencyWeeks: number;
  phoneNumber?: string | null;
  campaignStatus?: CampaignStatus | null;
}

export interface UpdateCampaignResponse {
  data: Campaign;
}
