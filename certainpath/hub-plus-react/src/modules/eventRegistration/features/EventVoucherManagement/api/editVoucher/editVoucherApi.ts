import axios from "../../../../../../api/axiosInstance";
import { EditVoucherRequest, EditVoucherResponse } from "./types";

export const updateVoucher = async (
  id: number,
  requestData: EditVoucherRequest,
): Promise<EditVoucherResponse> => {
  const response = await axios.put<EditVoucherResponse>(
    `/api/private/event-voucher/${id}/edit`,
    requestData,
  );
  return response.data;
};
