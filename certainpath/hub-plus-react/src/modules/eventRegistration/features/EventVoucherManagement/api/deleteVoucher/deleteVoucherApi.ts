import { DeleteVoucherResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const deleteVoucher = async (
  id: number,
): Promise<DeleteVoucherResponse> => {
  const response = await axios.delete<DeleteVoucherResponse>(
    `/api/private/event-voucher/${id}/delete`,
  );
  return response.data;
};
