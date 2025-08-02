export interface FetchStochasticProspectsRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "ASC" | "DESC";
}

export interface FetchStochasticProspectsResponse {
  data: StochasticProspect[];
  meta?: {
    totalCount: number;
  };
}

export interface StochasticProspect {
  id: number;
  fullName: string;
  firstName?: string | null;
  lastName?: string | null;
  isPreferred?: boolean | null;
  doNotMail?: boolean | null;
  doNotContact?: boolean | null;
  companyName: string;
  createdAt?: string | null;
  updatedAt?: string | null;
  address?: StochasticProspectAddress | null;
}

export interface StochasticProspectAddress {
  address1?: string | null;
  address2?: string | null;
  city?: string | null;
  stateCode?: string | null;
  postalCode?: string | null;
  countryCode?: string | null;
  isBusiness?: boolean | null;
  isVacant?: boolean | null;
  isDoNotMail?: boolean | null;
  isGlobalDoNotMail?: boolean | null;
}
