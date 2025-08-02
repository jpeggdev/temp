export interface CampaignProduct {
  category: string;
  id: number;
  name: string;
  type: string;
  description: string;
  isActive?: boolean;
  prospectPrice?: number;
  customerPrice?: number;
}

export interface FetchCampaignProductsResponse {
  data: {
    campaignProducts: CampaignProduct[];
  };
  meta?: {
    totalCount: number;
  };
}
