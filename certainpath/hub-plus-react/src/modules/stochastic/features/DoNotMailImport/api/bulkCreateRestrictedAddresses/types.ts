import { Address } from "@/modules/stochastic/features/DoNotMailImport/api/uploadDoNotMailList/types";

export interface bulkCreateRestrictedAddressesRequest {
  addresses: Address[];
}

export interface bulkCreateRestrictedAddressesResponse {
  message: string;
}
