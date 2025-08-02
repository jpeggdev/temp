export interface TradeItem {
  id: number;
  name: string;
}

export interface GetCreateUpdateEventMetadataResponse {
  data: {
    trades: TradeItem[];
  };
}
