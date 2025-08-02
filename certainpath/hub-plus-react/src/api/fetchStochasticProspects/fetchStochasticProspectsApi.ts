import axios from "../axiosInstance";
import {
  FetchStochasticProspectsRequest,
  FetchStochasticProspectsResponse,
} from "./types";

export const fetchStochasticProspects = async (
  requestData: FetchStochasticProspectsRequest,
): Promise<FetchStochasticProspectsResponse> => {
  const response = await axios.get<FetchStochasticProspectsResponse>(
    "/api/private/stochastic-prospects",
    {
      params: requestData,
    },
  );
  return response.data;
};
