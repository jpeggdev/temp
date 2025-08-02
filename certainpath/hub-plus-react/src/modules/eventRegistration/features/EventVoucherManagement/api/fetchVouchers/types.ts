import { Voucher } from "@/modules/eventRegistration/features/EventVoucherManagement/api/createVoucher/types";

export interface FetchVouchersRequest {
  page?: number;
  pageSize?: number;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
  searchTerm?: string;
  isActive?: number;
}

export interface FetchVouchersResponse {
  data: Voucher[];
  meta: {
    totalCount: number | null;
  };
}
