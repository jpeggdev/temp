export interface FetchCompaniesRequest {
  searchTerm?: string;
  page?: number;
  sortBy?: string;
  sortOrder?: "ASC" | "DESC";
  pageSize?: number;
}

export interface FetchCompaniesResponse {
  data: {
    companies: ApiCompany[];
  };
  meta?: {
    totalCount: number;
  };
}

export interface ApiCompany {
  id: number;
  companyName: string;
  uuid: string;
  salesforceId?: string | null;
  intacctId?: string | null;
  marketingEnabled?: boolean;
  isCertainPath?: boolean;
  createdAt?: string;
  updatedAt?: string;
}
