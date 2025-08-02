export interface FetchLifetimeValueChartDataRequest {
  tradeId?: string;
  cities?: string[];
  years?: string[];
}

export interface FilterOptions {
  years: string[];
  cities: string[];
  trades: string[];
  [key: string]: string[];
}

export interface LifetimeValueChartDataItem {
  salesPeriod: string;
  totalSales: number;
  salesPercentage: number;
  [key: string]: string | number;
}

export interface FetchLifetimeValueChartDataResponse {
  data: {
    chartData: LifetimeValueChartDataItem[];
    filterOptions: FilterOptions;
  };
}
