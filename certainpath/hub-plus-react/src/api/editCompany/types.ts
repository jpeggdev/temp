export interface EditCompanyDTO {
  marketingEnabled: boolean;
  companyName: string;
  salesforceId?: string | null;
  intacctId?: string | null;
  companyEmail?: string | null;
  websiteUrl?: string | null;
}

export interface EditCompanyResponse {
  data: {
    companyName: string;
    salesforceId: string | null;
    intacctId: string | null;
    companyEmail: string | null;
    marketingEnabled: boolean;
    fieldServiceSoftwareId: number | null;
    fieldServiceSoftwareName: string | null;
    websiteUrl: string | null;
  };
}
