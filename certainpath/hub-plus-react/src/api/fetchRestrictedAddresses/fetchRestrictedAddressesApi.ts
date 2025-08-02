import axios from "../axiosInstance";
import {
  FetchRestrictedAddressesRequest,
  FetchRestrictedAddressesResponse,
} from "./types";

export const fetchRestrictedAddresses = async (
  requestData: FetchRestrictedAddressesRequest,
): Promise<FetchRestrictedAddressesResponse> => {
  const response = await axios.get<FetchRestrictedAddressesResponse>(
    "/api/private/restricted-addresses",
    {
      params: requestData,
    },
  );
  return response.data;
};
