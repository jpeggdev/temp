export interface GetEditRolesAndPermissionsResponse {
  data: EditRolesAndPermissions;
}

export interface EditRolesAndPermissions {
  roles: BusinessRole[];
  permissions: Permission[];
}

export interface BusinessRole {
  id: number;
  name: string;
  label: string;
  description: string | null;
  isCertainPathOnly: boolean;
  permissions: RolePermission[];
}

export interface RolePermission {
  id: number;
  name: string;
  label: string;
  description: string;
  isCertainPathOnly: boolean;
}

export interface Permission {
  id: number;
  name: string;
  label: string;
  description: string;
  isCertainPathOnly: boolean;
}
