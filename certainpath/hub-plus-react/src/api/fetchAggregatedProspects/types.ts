export interface FetchAggregatedProspectsRequest {
  customerInclusionRule?: string;
  lifetimeValueRule?: string;
  clubMembersRule?: string;
  installationsRule?: string;
  prospectMinAgeRule?: number;
  prospectMaxAgeRule?: number;
  minEstimatedIncomeRule?: string;
  minHomeAgeRule?: number;
  tagsRule?: string;
  locations?: number[];
  addressTypeRule?: string;
}

export interface AggregatedProspect {
  postalCode: string;
  households: number;
  avgSales: number;
}

export interface FetchAggregatedProspectsResponse {
  data: AggregatedProspect[];
}
