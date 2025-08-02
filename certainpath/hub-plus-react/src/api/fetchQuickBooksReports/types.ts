export interface FetchQuickBooksReportsRequest {
  reportType?: ReportType;
  page?: number;
  pageSize?: number;
  sortOrder?: "ASC" | "DESC";
}

export enum ReportType {
  MONTHLY_BALANCE_SHEET = "monthly_balance_sheet",
  PROFIT_AND_LOSS = "profit_and_loss",
  TRANSACTION_LIST = "transaction_list",
}

export interface QuickBooksReport {
  name: string;
  date: string;
  uuid: string;
}

export interface FetchQuickBooksReportsResponse {
  data: QuickBooksReport[];
  meta?: {
    totalCount: number;
  };
}
