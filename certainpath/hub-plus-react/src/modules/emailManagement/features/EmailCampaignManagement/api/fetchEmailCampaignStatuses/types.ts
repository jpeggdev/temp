import { EmailCampaignStatus } from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/types";

export interface FetchEmailCampaignStatusesRequest {
  searchTerm?: string;
  page?: number;
  perPage?: number;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
}

export interface FetchEmailCampaignStatusesResponse {
  data: EmailCampaignStatus[];
  meta: {
    totalCount: number | null;
  };
}
