export interface GetEditUserDetailsResponse {
  data: EditUserDetails;
}

export interface EditUserDetails {
  firstName: string;
  lastName: string;
  email: string;
  employeeUuid: string;
  employeeBusinessRoleId: number | null;

  availableApplications: Application[];
  availableRoles: Role[];
  availablePermissions: PermissionGroup[];
  employeeApplicationAccess: ApplicationAccessRecord[];

  employeeRolePermissions: number[];
  employeeAdditionalPermissions: number[];
}

export interface Application {
  id: number;
  name: string;
}

export interface Role {
  id: number;
  name: string;
  label: string;
  description: string;
  isCertainPathOnly: boolean;
}

export interface PermissionGroup {
  groupName: string;
  permissions: Permission[];
}

export interface Permission {
  permissionId: number;
  name: string;
  label: string;
  description: string;
  isCertainPathOnly: boolean;
}

export interface ApplicationAccessRecord {
  applicationId: number;
  applicationName: string;
}
