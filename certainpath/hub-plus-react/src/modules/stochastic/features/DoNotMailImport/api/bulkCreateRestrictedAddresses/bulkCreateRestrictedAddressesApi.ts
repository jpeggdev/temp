import axios from "../../../../../../api/axiosInstance";
import {
  bulkCreateRestrictedAddressesRequest,
  bulkCreateRestrictedAddressesResponse,
} from "@/modules/stochastic/features/DoNotMailImport/api/bulkCreateRestrictedAddresses/types";

export const bulkCreateRestrictedAddresses = async (
  requestData: bulkCreateRestrictedAddressesRequest,
): Promise<bulkCreateRestrictedAddressesResponse> => {
  const response = await axios.post<bulkCreateRestrictedAddressesResponse>(
    "/api/private/restricted-addresses/bulk-create",
    requestData,
  );

  return response.data;
};
