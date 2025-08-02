import axios from "../../../../../../api/axiosInstance";
import {
  FetchTradeFilterOptionsRequest,
  FetchTradeFilterOptionsResponse,
} from "./types";

export const fetchTradesFilterOptions = async (
  requestData?: FetchTradeFilterOptionsRequest,
): Promise<FetchTradeFilterOptionsResponse> => {
  const response = await axios.get<FetchTradeFilterOptionsResponse>(
    `/api/private/filter-option/trades`,
    { params: requestData },
  );

  return response.data;
};
