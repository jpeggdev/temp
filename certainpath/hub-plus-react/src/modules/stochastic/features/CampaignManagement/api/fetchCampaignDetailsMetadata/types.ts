export interface KeyValueString {
  name: string;
  value: string;
}

export interface KeyValueNumber {
  name: string;
  value: number;
}

export interface CampaignDetailsMetadata {
  customerRestrictionCriteria: KeyValueString[];
  mailingFrequencies: KeyValueNumber[];
  campaignTargets: KeyValueString[];
  estimatedIncomeOptions: KeyValueNumber[];
  addressTypes: KeyValueString[];
}

export interface FetchCampaignDetailsMetadataResponse {
  data: CampaignDetailsMetadata;
}
