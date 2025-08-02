export interface FetchDashboardDataRequest {
  years?: string[];
  trades?: string[];
  cities?: string[];
  scope: string;
}

export interface LifetimeValueDataItem {
  salesPeriod: string;
  totalSales: number;
  salesPercentage: number;
  [key: string]: string | number;
}

export interface TotalSalesByYearAndMonthDataItem {
  month: string;
  [year: string]: number | string;
}

export interface TotalSalesByZipCodeDataItem {
  postalCode: string;
  totalSales: number;
  [key: string]: string | number;
}

export interface TotalSalesNewCustomerByZipCodeAndYearDataItem {
  postalCode: string;
  year: string;
  sales: number;
  [key: string]: string | number;
}

export interface TotalSalesNewVsExistingCustomerDataItem {
  HF: string;
  NC: string;
  total: string;
  year: string;
  [key: string]: string;
}

export interface PercentageOfNewCustomersByZipCodeDataItem {
  postalCode: string;
  [year: string]: number | string;
}

export interface CustomersAverageInvoiceComparisonData {
  chartData: {
    year: number;
    newCustomerAvg: number;
    repeatCustomerAvg: number;
  }[];
  avgSales: {
    newCustomerAvg: number;
    repeatCustomerAvg: number;
  };
}

export interface LifetimeValueByTierData {
  chartData: {
    tier: string;
    householdCount: number;
    totalSales: number;
  }[];
  totalHouseholdsCount: number;
}

export interface PercentageOfNewCustomersChangeByZipCodeDataItem {
  postalCode: string;
  [year: string]:
    | string
    | {
        ncCount: number;
        percentChange: number | null;
      };
}

export interface FetchDashboardDataResponse {
  data: {
    lifetimeValueData: LifetimeValueDataItem[];
    lifetimeValueByTierData: LifetimeValueByTierData;
    totalSalesByZipCodeData: TotalSalesByZipCodeDataItem[];
    totalSalesByYearAndMonthData: TotalSalesByYearAndMonthDataItem[];
    customersAverageInvoiceComparisonData: CustomersAverageInvoiceComparisonData;
    totalSalesNewVsExistingCustomerData: TotalSalesNewVsExistingCustomerDataItem[];
    percentageOfNewCustomersByZipCodeData: PercentageOfNewCustomersByZipCodeDataItem[];
    totalSalesNewCustomerByZipCodeAndYearData: TotalSalesNewCustomerByZipCodeAndYearDataItem[];
    percentageOfNewCustomersChangeByZipCodeData: PercentageOfNewCustomersChangeByZipCodeDataItem[];
  };
}
