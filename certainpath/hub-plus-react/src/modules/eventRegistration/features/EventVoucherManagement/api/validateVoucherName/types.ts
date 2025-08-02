export interface ValidateVoucherNameRequest {
  name: string;
}

export interface ValidateVoucherNameResponseData {
  nameExists: boolean;
  message?: string | null;
}

export interface ValidateVoucherNameResponse {
  data: ValidateVoucherNameResponseData;
}
