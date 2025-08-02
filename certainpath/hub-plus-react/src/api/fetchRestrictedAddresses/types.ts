export interface FetchRestrictedAddressesRequest {
  externalId?: string;
  address1?: string;
  address2?: string;
  city?: string;
  stateCode?: string;
  postalCode?: string;
  countryCode?: string;
  isBusiness?: "true" | "false";
  isVacant?: "true" | "false";
  isVerified?: "true" | "false";
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
  page?: number;
  perPage?: number;
}

export interface RestrictedAddress {
  id: number;
  address1: string;
  address2?: string | null;
  city: string;
  stateCode: string;
  postalCode: string;
  countryCode: string;
  isBusiness: boolean;
  isVacant: boolean;
  isVerified: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface FetchRestrictedAddressesResponse {
  data: RestrictedAddress[];
  meta: {
    totalCount: number | null;
  };
}
