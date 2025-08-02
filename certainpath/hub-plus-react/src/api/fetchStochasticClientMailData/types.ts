export interface StochasticClientMailDataRow {
  id: number;
  batchNumber: number;
  intacctId?: string;
  clientName?: string;
  productId?: number;
  campaignId?: number;
  campaignName?: string;
  batchStatus?: string;
  prospectCount?: number;
  week?: number;
  year?: number;
  startDate?: string;
  endDate?: string;
  campaignProduct?: {
    id: number;
    name: string;
    type: string;
    description: string;
    code: string;
    distributionMethod: string;
    mailerDescription: string;
    hasColoredStock: boolean;
    category: string;
    subCategory: string | null;
    format: string;
    prospectPrice: string;
    customerPrice: string;
    brand: string | null;
    size: string;
    targetAudience: string;
    createdAt: string;
    updatedAt: string;
  };
  batchPricing?: {
    batchPostageId: number;
    reference: string;
    postageExpense: number;
    materialExpense: number;
    totalExpense: number;
    pricePerPiece: number;
    actualQuantity: number;
    projectedQuantity: number;
    canBeBilled: boolean;
  };
}

export interface FetchStochasticClientMailDataRequest {
  week?: number;
  year?: number;
  page?: number;
  perPage?: number;
  sortOrder?: "ASC" | "DESC";
  isCsv?: boolean;
}

export interface FetchStochasticClientMailDataResponse {
  data: StochasticClientMailDataRow[];
  meta?: {
    totalCount?: number;
  };
}
