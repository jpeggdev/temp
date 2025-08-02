export interface GetMyCompanyProfileResponse {
  data: MyCompanyProfile;
}

export interface MyCompanyProfile {
  companyName: string;
  companyEmail: string | null;
  websiteUrl: string | null;
  addressLine1: string | null;
  addressLine2: string | null;
  city: string | null;
  state: string | null;
  country: string | null;
  zipCode: string | null;
  isMailingAddressSame: boolean;
  mailingAddressLine1: string | null;
  mailingAddressLine2: string | null;
  mailingState: string | null;
  mailingCountry: string | null;
  mailingZipCode: string | null;
}
