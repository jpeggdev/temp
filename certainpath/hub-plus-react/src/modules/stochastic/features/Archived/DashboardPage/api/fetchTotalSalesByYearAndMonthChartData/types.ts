export interface FetchTotalSalesByYearAndMonthChartDataResponse {
  data: TotalSalesByYearAndMonthChartDataItem[];
}

export interface TotalSalesByYearAndMonthChartDataItem {
  month: string;
  [year: string]: number | string;
}
