import { FetchVoucherResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchVoucher = async (
  id: number,
): Promise<FetchVoucherResponse> => {
  const response = await axios.get<FetchVoucherResponse>(
    `/api/private/event-voucher/${id}`,
  );
  return response.data;
};
