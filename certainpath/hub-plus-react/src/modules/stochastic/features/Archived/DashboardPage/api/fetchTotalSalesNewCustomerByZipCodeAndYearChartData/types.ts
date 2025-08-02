export interface FetchTotalSalesNewCustomerByZipCodeAndYearChartDataRequest {
  tradeId?: string;
  cities?: string[];
  years?: string[];
}

export interface FetchTotalSalesNewCustomerByZipCodeAndYearChartDataResponse {
  data: {
    chartData: TotalSalesNewCustomerByZipCodeAndYearChartDataItem[];
    filterOptions: FilterOptions;
  };
}

export interface FilterOptions {
  years: string[];
  cities: string[];
  trades: string[];
  [key: string]: string[];
}

export interface TotalSalesNewCustomerByZipCodeAndYearChartDataItem {
  postalCode: string;
  year: string;
  sales: number;
}
