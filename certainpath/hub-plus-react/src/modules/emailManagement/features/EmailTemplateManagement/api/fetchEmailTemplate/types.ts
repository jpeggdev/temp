import { EmailTemplateCategory } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplateCategories/types";

export interface EmailTemplate {
  id: number;
  templateName: string;
  emailSubject: string;
  emailContent: string;
  isActive: boolean;
  emailTemplateCategories?: EmailTemplateCategory[];
  createdAt?: string;
  updatedAt?: string;
}

export interface FetchEmailTemplateResponse {
  data: EmailTemplate;
}
