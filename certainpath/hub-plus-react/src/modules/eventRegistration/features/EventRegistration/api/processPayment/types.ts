export interface ProcessPaymentRequest {
  dataDescriptor: string;
  dataValue: string;
  amount: number;
  shouldCreatePaymentProfile: boolean;
  invoiceNumber: string;
  voucherQuantity?: number;
  discountCode?: string;
  discountAmount?: number;
  adminDiscountType?: "percentage" | "fixed_amount";
  adminDiscountValue?: number;
  adminDiscountReason?: string;
  eventCheckoutSessionUuid?: string;
}

export interface ProcessPaymentResponseData {
  transactionId?: string | null;
  success: boolean;
}

export interface ProcessPaymentResponse {
  data: ProcessPaymentResponseData;
}
