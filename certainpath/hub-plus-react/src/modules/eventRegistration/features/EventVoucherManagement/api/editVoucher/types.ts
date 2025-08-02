import { Voucher } from "@/modules/eventRegistration/features/EventVoucherManagement/api/createVoucher/types";

export interface EditVoucherRequest {
  name: string;
  description?: string | null;
  companyIdentifier: string;
  isActive: boolean;
  startDate?: string | null | undefined;
  endDate?: string | null | undefined;
}

export interface EditVoucherResponse {
  data: Voucher;
}
