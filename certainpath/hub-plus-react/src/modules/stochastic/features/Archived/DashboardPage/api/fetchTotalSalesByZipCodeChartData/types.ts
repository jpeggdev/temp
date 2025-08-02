export interface FetchTotalSalesByZipCodeChartDataRequest {
  tradeId?: string;
  cities?: string[];
  years?: string[];
}

export interface FetchTotalSalesByZipCodeChartDataResponse {
  data: {
    chartData: TotalSalesByZipCodeChartDataItem[];
    filterOptions: FilterOptions;
  };
}

export interface FilterOptions {
  years: string[];
  cities: string[];
  trades: string[];
  [key: string]: string[];
}

export interface TotalSalesByZipCodeChartDataItem {
  postalCode: string;
  totalSales: number;
  [key: string]: string | number;
}
