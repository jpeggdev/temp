export interface GetBatchProspectsRequest {
  page: number;
  perPage: number;
  sortOrder?: "ASC" | "DESC";
}

export interface Prospect {
  id: number;
  fullName: string | null;
  firstName: string | null;
  lastName: string | null;
  address1?: string | null;
  address2?: string | null;
  city?: string | null;
  state?: string | null;
  postalCode?: string | null;
  doNotMail: boolean | null;
  doNotContact: boolean | null;
  externalId?: string | null;
  isPreferred: boolean | null;
  isActive: boolean | null;
  isDeleted: boolean | null;
  companyId?: number | null;
  customerId?: number | null;
}

export interface GetBatchProspectsResponse {
  data: Prospect[];
  meta: {
    totalCount: number | null;
  };
}
