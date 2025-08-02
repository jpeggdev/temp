export interface GetEditCompanyDetailsResponse {
  data: EditCompanyDetails;
}

export interface EditCompanyDetails {
  companyName: string;
  salesforceId: string | null;
  intacctId: string | null;
  marketingEnabled: boolean;
  companyEmail: string | null;
  fieldServiceSoftwareId: number | null;
  fieldServiceSoftwareName: string | null;
  websiteUrl: string | null;
  fieldServiceSoftwareList: FieldServiceSoftware[];
  tradeList: Trade[];
  companyTradeIds: number[];
}

export interface FieldServiceSoftware {
  id: number;
  name: string;
}

export interface Trade {
  id: number;
  name: string;
  description: string;
}
