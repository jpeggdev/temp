import axios from "../axiosInstance";
import {
  FetchStochasticClientMailDataRequest,
  FetchStochasticClientMailDataResponse,
} from "./types";

export const fetchStochasticClientMailData = async (
  requestParams: FetchStochasticClientMailDataRequest = {},
): Promise<FetchStochasticClientMailDataResponse> => {
  const response = await axios.get<FetchStochasticClientMailDataResponse>(
    `/api/private/stochastic-client-mail-data`,
    {
      params: requestParams,
    },
  );

  return response.data;
};
