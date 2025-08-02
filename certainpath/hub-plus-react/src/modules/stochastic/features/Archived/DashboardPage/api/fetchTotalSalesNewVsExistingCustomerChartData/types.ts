export interface FetchTotalSalesNewVsExistingCustomerChartDataResponse {
  data: TotalSalesNewVsExistingCustomerChartDataItem[];
}

export interface TotalSalesNewVsExistingCustomerChartDataItem {
  HF: string;
  NC: string;
  total: string;
  year: string;
  [key: string]: string;
}
