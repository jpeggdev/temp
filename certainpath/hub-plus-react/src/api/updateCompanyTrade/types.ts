export interface UpdateCompanyTradeDTO {
  tradeId: number;
}

export interface UpdateCompanyTradeResponse {
  data: {
    message: string;
    tradeIds: number[];
  };
}
