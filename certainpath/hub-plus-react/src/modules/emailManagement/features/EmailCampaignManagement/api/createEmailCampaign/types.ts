import { EventSummary } from "@/modules/eventRegistration/features/EventManagement/api/fetchEvents/types";
import { EmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplate/types";
import { SessionSummary } from "@/modules/eventRegistration/features/EventSessionManagement/api/fetchEventSessions/types";
import { EmailCampaignSendOption } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaignSendOptions/types";

export interface CreateEmailCampaignRequest {
  campaignName: string;
  description?: string | null;
  emailSubject?: string | null;
  emailTemplateId: number;
  eventId: number;
  sessionId: number;
  sendOption: string;
}

export interface EmailCampaignStatus {
  id: number;
  name: string;
  displayName: string;
}

export interface EmailCampaign {
  id: number;
  campaignName: string;
  emailCampaignStatus: EmailCampaignStatus;
  event: EventSummary;
  eventSession: SessionSummary;
  emailTemplate: EmailTemplate;
  sendOption: EmailCampaignSendOption;
  emailSubject?: string;
  description?: string | null;
  dateSent?: string;
  createdAt?: string;
  updatedAt?: string;
}

export interface CreateEmailCampaignResponse {
  data: EmailCampaign;
}
