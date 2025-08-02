export interface Company {
  companyUuid: string;
  companyName: string;
  intacctId: string;
}

export interface MyCompaniesResponse {
  data: Company[];
}
