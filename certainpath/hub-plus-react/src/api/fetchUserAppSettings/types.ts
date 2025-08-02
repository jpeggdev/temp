export interface FetchUserAppSettingsResponse {
  data: UserAppSettings;
}

export interface UserAppSettings {
  userId: number;
  email: string;
  firstName: string;
  lastName: string;
  employeeUuid: string;
  companyName: string;
  companyId: number;
  intacctId: string;
  roleName: string;
  permissions: string[];
  applicationAccess: string[];
  isCertainPathCompany: boolean;
  legacyBannerToggle: boolean;
}
