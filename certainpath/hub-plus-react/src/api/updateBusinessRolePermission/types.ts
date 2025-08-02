export interface UpdateBusinessRolePermissionRequest {
  roleId: number;
  permissionIds: number[];
}

export interface UpdateBusinessRolePermissionResponse {
  data: {
    message: string;
  };
}
