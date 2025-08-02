export interface DiscountType {
  id: number;
  name: string;
  displayName: string;
  isDefault: boolean;
}

export interface DiscountMetadata {
  discountTypes: DiscountType[];
}

export interface FetchDiscountMetadataResponse {
  data: DiscountMetadata;
}
