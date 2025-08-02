export interface FetchStochasticCustomersRequest {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "ASC" | "DESC";
  isActive?: number;
}

export interface FetchStochasticCustomersResponse {
  data: StochasticCustomer[];
  meta?: {
    totalCount: number;
  };
}

export interface StochasticCustomer {
  id: number;
  name: string;
  isNewCustomer?: boolean | null;
  isRepeatCustomer?: boolean | null;
  hasInstallation?: boolean | null;
  hasSubscription?: boolean | null;
  countInvoices?: number | null;
  balanceTotal?: string | null;
  invoiceTotal?: string | null;
  lifetimeValue?: string | null;
  firstInvoicedAt?: string | null;
  lastInvoicedAt?: string | null;
  companyName: string;
  address?: StochasticCustomerAddress | null;
  doNotMail?: boolean | null;
}

export interface StochasticCustomerAddress {
  address1?: string | null;
  address2?: string | null;
  city?: string | null;
  stateCode?: string | null;
  postalCode?: string | null;
  countryCode?: string | null;
  isBusiness?: boolean | null;
  isVacant?: boolean | null;
  isDoNotMail?: boolean | null;
  isGlobalDoNotMail?: boolean | null;
}
