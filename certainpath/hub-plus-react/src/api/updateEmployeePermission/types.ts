export interface UpdateEmployeePermissionRequest {
  permissionId: number;
  active: boolean;
}

export interface UpdateEmployeePermissionResponse {
  message: string;
}
