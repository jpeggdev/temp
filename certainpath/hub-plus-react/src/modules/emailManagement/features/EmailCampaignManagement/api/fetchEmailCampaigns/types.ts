import { EmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/types";

export interface FetchEmailCampaignsRequest {
  page?: number;
  pageSize?: number;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
  searchTerm?: string;
  emailCampaignStatusId?: number;
}

export interface FetchEmailCampaignsResponse {
  data: EmailCampaign[];
  meta: {
    totalCount: number | null;
  };
}
