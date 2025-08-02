export interface UpdateRestrictedAddressRequest {
  address1: string;
  address2?: string | null;
  city: string;
  stateCode: string;
  postalCode: string;
  countryCode: string;
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

export interface UpdateRestrictedAddressResponse {
  data: RestrictedAddress;
}
