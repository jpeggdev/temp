export interface CreateCompanyRequest {
  companyName: string;
  websiteUrl: string | null;
  salesforceId?: string | null;
  intacctId?: string | null;
  companyEmail?: string | null;
}

export interface CreateCompanyResponse {
  data: {
    id: number | null;
    companyName: string | null;
    websiteUrl: string | null;
    uuid: string | null;
    salesforceId?: string | null;
    intacctId?: string | null;
    companyEmail?: string | null;
  };
}
