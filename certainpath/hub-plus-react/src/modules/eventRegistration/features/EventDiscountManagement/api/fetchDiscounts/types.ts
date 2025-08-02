import { Discount } from "@/modules/eventRegistration/features/EventDiscountManagement/api/createDiscount/types";

export interface FetchDiscountsRequest {
  page?: number;
  pageSize?: number;
  sortOrder?: "ASC" | "DESC";
  sortBy?: string;
  searchTerm?: string;
  isActive?: number;
}

export interface FetchDiscountsResponse {
  data: Discount[];
  meta: {
    totalCount: number | null;
  };
}
