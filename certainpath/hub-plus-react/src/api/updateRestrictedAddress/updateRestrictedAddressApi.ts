import axios from "../axiosInstance";
import {
  UpdateRestrictedAddressRequest,
  UpdateRestrictedAddressResponse,
} from "./types";

export const updateRestrictedAddress = async (
  id: number,
  requestData: UpdateRestrictedAddressRequest,
): Promise<UpdateRestrictedAddressResponse> => {
  const response = await axios.put<UpdateRestrictedAddressResponse>(
    `/api/private/restricted-addresses/${id}`,
    requestData,
  );
  return response.data;
};
