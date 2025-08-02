export interface CreateBatchInvoiceRequest {
  accountIdentifier: string;
  type: string;
  quantityMailed: string;
  serviceUnitPrice: string;
  postageUnitPrice: string;
  batchReference: string;
}

export interface CreateBatchInvoiceResponse {
  data: {
    accountIdentifier: string;
    type: string;
    quantityMailed: string;
    serviceUnitPrice: string;
    postageUnitPrice: string;
    batchReference: string;
    invoiceReference: string | null;
  };
}
