import axios from "../axiosInstance";
import { FetchStochasticClientMailDataRequest } from "./types";

export const fetchStochasticClientMailDataCsv = async (
  requestParams: FetchStochasticClientMailDataRequest = {},
): Promise<Blob> => {
  const response = await axios.get<Blob>(
    `/api/private/stochastic-client-mail-data`,
    {
      responseType: "blob",
      headers: {
        Accept: "text/csv",
      },
      params: requestParams,
    },
  );

  return response.data;
};
