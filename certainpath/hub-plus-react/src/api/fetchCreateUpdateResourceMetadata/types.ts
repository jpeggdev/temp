export interface ResourceTag {
  id: number;
  name: string;
}

export interface ResourceCategory {
  id: number;
  name: string;
}

export interface EmployeeRole {
  id: number;
  name: string;
}

export interface Trade {
  id: number;
  name: string;
}

export interface ResourceType {
  id: number;
  name: string;
  requiresContentUrl: boolean;
  isDefault: boolean;
}

export interface CreateUpdateResourceMetadata {
  resourceTags: ResourceTag[];
  resourceCategories: ResourceCategory[];
  employeeRoles: EmployeeRole[];
  trades: Trade[];
  resourceTypes: ResourceType[];
}

export interface FetchCreateUpdateResourceMetadataResponse {
  data: CreateUpdateResourceMetadata;
}
