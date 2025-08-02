import { EmailTemplateCategory } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplateCategories/types";

export interface UpdateEmailTemplateRequest {
  templateName: string;
  emailSubject: string;
  emailContent: string;
  categoryIds?: number[];
}

export interface UpdateEmailTemplateResponse {
  data: {
    id: number;
    templateName: string;
    emailSubject: string;
    emailContent: string;
    isActive: boolean;
    emailTemplateCategories?: EmailTemplateCategory[];
    createdAt?: string;
    updatedAt?: string;
  };
}
