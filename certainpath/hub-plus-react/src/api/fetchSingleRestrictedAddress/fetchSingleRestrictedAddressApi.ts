import axios from "../axiosInstance";
import { FetchSingleRestrictedAddressResponse } from "./types";

export const fetchSingleRestrictedAddressById = async (
  id: number,
): Promise<FetchSingleRestrictedAddressResponse> => {
  const response = await axios.get<FetchSingleRestrictedAddressResponse>(
    `/api/private/restricted-addresses/${id}`,
  );
  return response.data;
};
