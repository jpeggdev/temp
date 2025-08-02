import axios from "../axiosInstance";
import {
  FetchAggregatedProspectsRequest,
  FetchAggregatedProspectsResponse,
} from "./types";

export const fetchAggregatedProspects = async (
  requestParams: FetchAggregatedProspectsRequest = {},
): Promise<FetchAggregatedProspectsResponse> => {
  const response = await axios.get<FetchAggregatedProspectsResponse>(
    `/api/private/company/aggregated-prospects`,
    {
      params: requestParams,
    },
  );
  return response.data;
};
