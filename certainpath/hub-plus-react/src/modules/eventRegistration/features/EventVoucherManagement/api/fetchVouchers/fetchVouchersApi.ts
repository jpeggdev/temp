import { FetchVouchersRequest, FetchVouchersResponse } from "./types";
import axios from "../../../../../../api/axiosInstance";

export const fetchVouchers = async (
  requestData: FetchVouchersRequest,
): Promise<FetchVouchersResponse> => {
  const response = await axios.get<FetchVouchersResponse>(
    "/api/private/event-vouchers",
    { params: requestData },
  );
  return response.data;
};
