import axios from "../axiosInstance";
import { DeleteRestrictedAddressResponse } from "./types";

export const deleteRestrictedAddress = async (
  id: number,
): Promise<DeleteRestrictedAddressResponse> => {
  const response = await axios.delete<DeleteRestrictedAddressResponse>(
    `/api/private/restricted-addresses/${id}`,
  );
  return response.data;
};
