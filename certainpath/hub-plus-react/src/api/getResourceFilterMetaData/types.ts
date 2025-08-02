export interface ResourceTypeFilterMetaDataItem {
  id: number;
  name: string;
  resourceCount: number;
}

export interface EmployeeRoleFilterMetaDataItem {
  id: number;
  name: string;
}

export interface TradeFilterMetaDataItem {
  id: number;
  name: string;
}

export interface GetResourceFilterMetaDataAPIResponse {
  data: {
    resourceTypes: ResourceTypeFilterMetaDataItem[];
    employeeRoles: EmployeeRoleFilterMetaDataItem[];
    trades: TradeFilterMetaDataItem[];
  };
}
