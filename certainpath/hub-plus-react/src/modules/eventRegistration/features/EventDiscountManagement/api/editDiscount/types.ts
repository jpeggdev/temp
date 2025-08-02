import { Discount } from "@/modules/eventRegistration/features/EventDiscountManagement/api/createDiscount/types";

export interface EditDiscountRequest {
  code: string;
  description?: string | null;
  discountTypeId: number | string | null;
  discountValue: string | null;
  maximumPurchaseAmount?: number | string | null;
  startDate?: string | null;
  endDate?: string | null;
  eventIds?: number[];
}

export interface EditDiscountResponse {
  data: Discount;
}
