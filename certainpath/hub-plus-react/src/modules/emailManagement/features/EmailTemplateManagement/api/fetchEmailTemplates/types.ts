import { EmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplate/types";

export interface FetchEmailTemplatesRequest {
  searchTerm?: string;
  page?: number;
  perPage?: number;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
}

export interface FetchEmailTemplatesResponse {
  data: EmailTemplate[];
  meta: {
    totalCount: number | null;
  };
}
