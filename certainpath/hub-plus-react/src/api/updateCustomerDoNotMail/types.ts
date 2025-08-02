export interface UpdateCustomerDoNotMailRequest {
  doNotMail: boolean;
}

export interface UpdateCustomerDoNotMailResponse {
  success: boolean;
  message?: string;
}
