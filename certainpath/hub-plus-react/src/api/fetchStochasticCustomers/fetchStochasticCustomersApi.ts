import axios from "../axiosInstance";
import {
  FetchStochasticCustomersRequest,
  FetchStochasticCustomersResponse,
} from "./types";

export const fetchStochasticCustomers = async (
  requestData: FetchStochasticCustomersRequest,
): Promise<FetchStochasticCustomersResponse> => {
  const response = await axios.get<FetchStochasticCustomersResponse>(
    "/api/private/stochastic-customers",
    {
      params: requestData,
    },
  );
  return response.data;
};
