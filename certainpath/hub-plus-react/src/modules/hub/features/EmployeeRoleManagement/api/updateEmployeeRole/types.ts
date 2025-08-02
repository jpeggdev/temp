export interface UpdateEmployeeRoleRequest {
  name: string;
}

export interface UpdateEmployeeRoleResponse {
  data: {
    id: number | null;
    name: string | null;
  };
}
