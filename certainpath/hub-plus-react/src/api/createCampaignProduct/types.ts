export interface CreateCampaignProductRequest {
  name: string;
  description: string;
  prospectPrice?: number;
  customerPrice?: number;
  isActive: boolean;
  category?: string;
}
