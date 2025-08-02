import { SingleEventData } from "@/modules/eventRegistration/features/EventManagement/api/fetchEvent/types";

export interface CreateDiscountRequest {
  code: string;
  description?: string | null;
  discountTypeId: number;
  discountValue: string | null;
  maximumUses?: number | null;
  minimumPurchaseAmount?: string | null;
  eventIds?: number[];
  startDate?: string | null;
  endDate?: string | null;
}

export interface DiscountType {
  id: number;
  name: string;
  displayName: string;
}

export interface Discount {
  id: number;
  code: string;
  description?: string | null;
  discountValue: string;
  discountType: DiscountType;
  usage: string;
  maximumUses?: number | null;
  minimumPurchaseAmount?: string | null;
  isActive: boolean;
  events?: SingleEventData[];
  startDate?: string | null;
  endDate?: string | null;
  createdAt: string;
  updatedAt: string;
  deletedAt?: string;
}

export interface CreateDiscountResponse {
  data: Discount;
}
