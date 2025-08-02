export interface ValidateDiscountCodeRequest {
  code: string;
}

export interface ValidateDiscountCodeResponseData {
  codeExists: boolean;
  message?: string | null;
}

export interface ValidateDiscountCodeResponse {
  data: ValidateDiscountCodeResponseData;
}
