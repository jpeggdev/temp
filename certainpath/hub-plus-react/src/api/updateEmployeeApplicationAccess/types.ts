export interface UpdateEmployeeApplicationAccessRequest {
  applicationId: number;
  active: boolean;
}

export interface UpdateEmployeeApplicationAccessResponse {
  message: string;
}
