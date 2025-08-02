import axios from "../../../../../../api/axiosInstance";
import { CreateVoucherRequest, CreateVoucherResponse } from "./types";

export const createVoucher = async (
  requestData: CreateVoucherRequest,
): Promise<CreateVoucherResponse> => {
  const response = await axios.post<CreateVoucherResponse>(
    "/api/private/event-voucher/create",
    requestData,
  );
  return response.data;
};
