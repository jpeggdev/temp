import { EmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/types";

export interface UpdateEmailCampaignRequest {
  campaignName: string;
  description?: string | null;
  emailSubject?: string | null;
  emailTemplateId: number;
  eventId: number;
  sessionId: number;
  sendOption: string;
}

export interface CreateEmailCampaignResponse {
  data: EmailCampaign;
}
