import { CampaignProduct } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";

export interface FilterCriteria {
  prospectAge: {
    min: string;
    max: string;
  };
  audience: string;
  estimatedIncome: string;
  homeAge: string;
  excludeClubMembers: boolean;
  excludeLTV: boolean;
  excludeInstallCustomers: boolean;
}

export interface ZipCode {
  code: string;
  avgSale: number;
  availableProspects: number;
  selectedProspects: string;
  filteredProspects: number;
}

export interface CreateCampaignRequest {
  campaignName: string;
  campaignProduct: CampaignProduct;
  description?: string | null;
  phoneNumber?: string | null;
  startDate: string;
  endDate: string;
  mailingFrequency: number;
  selectedMailingWeeks: number[];
  locations: number[];
  filterCriteria: FilterCriteria;
  zipCodes: ZipCode[];
  tags: string;
}

export interface CampaignStatus {
  id: number | null;
  name: string | null;
}

export interface MailPackage {
  id: number;
  name: string;
  series: string;
  externalId: string | null;
  isActive: boolean;
  isDeleted: boolean;
}

export interface Campaign {
  id: number;
  companyId: number;
  name: string;
  description?: string | null;
  startDate: string;
  endDate: string;
  mailingFrequencyWeeks: number;
  phoneNumber?: string | null;
  campaignStatus?: CampaignStatus | null;
  mailPackage?: MailPackage | null;
}

export interface CreateCampaignResponse {
  data: Campaign;
}
