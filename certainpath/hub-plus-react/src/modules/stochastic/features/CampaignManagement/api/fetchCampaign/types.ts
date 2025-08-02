export interface CampaignStatus {
  id: number | null;
  name: string | null;
}

export interface Campaign {
  id: number;
  companyId: number;
  name: string;
  description?: string | null;
  startDate?: string | null;
  endDate?: string | null;
  mailingIterationWeeks?: number | null;
  phoneNumber?: string | null;
  campaignStatus?: CampaignStatus | null;
  campaignProduct?: {
    name: string | null;
  } | null;
  campaignPricing?: {
    postageExpense: number | null;
    materialExpense: number | null;
    totalExpense: number | null;
    actualQuantity: number | null;
    projectedQuantity: number | null;
  } | null;
}

export interface FetchCampaignResponse {
  data: Campaign;
}
