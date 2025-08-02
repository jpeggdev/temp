export interface CampaignTarget {
  name: string;
  value: string;
}

export interface AddressType {
  name: string;
  value: string;
}

export interface DemographicTarget {
  name: string;
  value: string;
}

export interface Tag {
  name: string;
}

export interface CustomerRestrictionCriterion {
  name: string;
  value: string;
}

export interface Location {
  id?: string;
  name?: string;
}

export interface MailingFrequency {
  value: number;
  label: string;
}

export interface MailingDropWeek {
  weekNumber: number;
  mailingCount: number;
}

export interface MailingSchedule {
  mailingFrequency: MailingFrequency;
  mailingDropWeeks: MailingDropWeek[];
}

export interface PostalCodeLimit {
  postalCode: number;
  limit: number;
}

export interface CampaignFilters {
  tags: Tag[];
  campaignTarget: CampaignTarget;
  addressType: AddressType;
  demographicTargets: DemographicTarget[];
  customerRestrictionCriteria: CustomerRestrictionCriterion[];
}

export interface CampaignStatus {
  id: number;
  name: string;
}

export interface CampaignProduct {
  id: number;
  name: string;
}

export interface CampaignDetailsData {
  id: number;
  intacctId: string;
  name: string;
  phoneNumber: string;
  description: string;
  startDate: string;
  endDate: string;
  campaignStatus: CampaignStatus;
  campaignProduct: CampaignProduct;
  locations: Location[];
  mailingSchedule: MailingSchedule;
  filters: CampaignFilters;
  postalCodeLimits: PostalCodeLimit[];
  totalProspects: number;
  canBePaused: boolean;
  canBeStopped: boolean;
  canBeResumed: boolean;
  showDemographicTargets: boolean;
  showTagSelector: boolean;
  showCustomerRestrictionCriteria: boolean;
}

export interface GetCampaignDetailsRequest {
  campaignId: string;
}

export interface GetCampaignDetailsResponse {
  data: CampaignDetailsData;
}
