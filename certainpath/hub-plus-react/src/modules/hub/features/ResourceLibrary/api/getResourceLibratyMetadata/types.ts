export interface Trade {
  id: number;
  name: string;
  icon: string | null;
}
export interface ResourceType {
  id: number;
  name: string;
  resourceCount: number;
  icon: string | null;
}

export interface ResourceCategory {
  id: number;
  name: string;
}

export interface EmployeeRole {
  id: number;
  name: string;
}

export interface ResourceLibraryMetadataFilters {
  trades: Trade[];
  employeeRoles: EmployeeRole[];
  resourceTypes: ResourceType[];
  resourceCategories: ResourceCategory[];
}

export interface GetResourceLibraryMetadataResponse {
  data: {
    filters: ResourceLibraryMetadataFilters;
  };
}
