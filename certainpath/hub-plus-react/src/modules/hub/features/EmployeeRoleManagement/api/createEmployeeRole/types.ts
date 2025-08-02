export interface CreateEmployeeRoleRequest {
  name: string;
}

export interface CreateEmployeeRoleResponse {
  data: {
    id: number | null;
    name: string | null;
  };
}
