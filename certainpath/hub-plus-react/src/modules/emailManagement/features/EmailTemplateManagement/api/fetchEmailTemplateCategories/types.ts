import { Color } from "@/components/EntityPickerModal/EntityPickerModal";

export interface fetchEmailTemplateCategoriesRequest {
  name?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

export interface EmailTemplateCategory {
  id: number;
  name: string;
  displayedName: string;
  color: Color;
}

export interface fetchEmailTemplateCategoriesResponse {
  data: EmailTemplateCategory[];
  meta?: {
    totalCount: number;
  };
}
