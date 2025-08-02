export interface fetchEmailTemplateVariablesRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

export interface EmailTemplateVariable {
  id: number;
  name: string;
  description?: string | null;
}

export interface fetchEmailTemplateVariablesResponse {
  data: EmailTemplateVariable[];
  meta?: {
    totalCount: number;
  };
}
