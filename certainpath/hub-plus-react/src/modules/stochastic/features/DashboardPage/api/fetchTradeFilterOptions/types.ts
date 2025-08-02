export interface FetchTradeFilterOptionsRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "name";
  sortOrder?: "ASC" | "DESC";
}
export interface TradeFilterOption {
  id: number;
  name: string;
}

export interface FetchTradeFilterOptionsResponse {
  data: TradeFilterOption[];
  meta?: {
    totalCount: number;
  };
}
