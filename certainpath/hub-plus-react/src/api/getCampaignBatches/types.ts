export interface GetCampaignBatchesRequest {
  page: number;
  perPage: number;
  sortOrder?: "ASC" | "DESC";
}

export interface BatchStatus {
  id: number | null;
  name: string | null;
}

export interface CampaignStatus {
  id: number | null;
  name: string | null;
}

export interface Campaign {
  id: number;
  name: string;
  description?: string | null;
  startDate?: string | null;
  endDate?: string | null;
  mailingIterationWeeks?: number | null;
  phoneNumber?: string | null;
  campaignStatus?: CampaignStatus | null;
}

export interface CampaignIteration {
  id: number;
  campaignId: number;
  campaignIterationStatusId: number;
  iterationNumber: number;
  startDate: string | null;
  endDate: string | null;
}

export interface CampaignIterationWeek {
  id: number;
  campaign_iteration_id: number;
  week_number: number;
  start_date: string | null;
  end_date: string | null;
}

export interface Batch {
  id: number;
  name: string;
  description?: string | null;
  prospectsCount?: number | null;
  batchStatus?: BatchStatus | null;
  campaign?: Campaign | null;
  campaignIteration?: CampaignIteration | null;
  campaignIterationWeek?: CampaignIterationWeek | null;
}

export interface GetCampaignBatchesResponse {
  data: Batch[];
  meta: {
    totalCount: number | null;
  };
}
