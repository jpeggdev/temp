import { emailEventPeriodFilter } from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogMetadata/types";

export interface FetchEmailCampaignEventLogsRequest {
  searchTerm?: string;
  emailEventPeriodFilter: emailEventPeriodFilter | undefined;
  page?: number;
  perPage?: number;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
}

export interface EmailCampaignEventLog {
  id: number;
  messageId: string;
  email: string;
  subject: string;
  isSent: boolean;
  isDelivered: boolean;
  isOpened: boolean;
  isClicked: boolean;
  isBounced: boolean;
  isMarkedAsSpam: boolean;
  eventSentAt: string;
}

export interface FetchEmailCampaignEventLogsResponse {
  data: EmailCampaignEventLog[];
  meta: {
    totalCount: number | null;
  };
}
