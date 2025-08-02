export interface FetchStochasticClientMailDataRequest {
  week?: number;
  year?: number;
  page?: number;
  perPage?: number;
  sortOrder?: "ASC" | "DESC";
  isCsv?: boolean;
}
