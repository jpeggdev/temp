export interface GetEmployeeRolesRequest {
  name?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

export interface EmployeeRoleDTO {
  id: number;
  name: string;
}

export interface GetEmployeeRolesResponse {
  data: {
    roles: EmployeeRoleDTO[];
    totalCount: number;
  };
}
