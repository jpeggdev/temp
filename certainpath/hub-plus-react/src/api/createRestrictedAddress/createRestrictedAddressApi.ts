import axios from "../axiosInstance";
import {
  CreateRestrictedAddressRequest,
  CreateRestrictedAddressResponse,
} from "./types";

export const createRestrictedAddress = async (
  requestData: CreateRestrictedAddressRequest,
): Promise<CreateRestrictedAddressResponse> => {
  const response = await axios.post<CreateRestrictedAddressResponse>(
    "/api/private/restricted-addresses",
    requestData,
  );
  return response.data;
};
