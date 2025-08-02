import { EmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplate/types";

export interface CreateEmailTemplateRequest {
  templateName: string;
  emailSubject: string;
  emailContent: string;
  categoryIds: number[];
}

export interface CreateEmailTemplateResponse {
  data: EmailTemplate;
}
