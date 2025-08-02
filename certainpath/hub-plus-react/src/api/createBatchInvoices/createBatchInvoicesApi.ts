import axios from "../axiosInstance";
import { CreateBatchInvoiceRequest, CreateBatchInvoiceResponse } from "./types";

export const createBatchInvoices = async (
  requestData: CreateBatchInvoiceRequest[],
): Promise<CreateBatchInvoiceResponse> => {
  const response = await axios.post<CreateBatchInvoiceResponse>(
    `/api/private/invoice/campaigns`,
    { invoices: requestData },
  );
  return response.data;
};
