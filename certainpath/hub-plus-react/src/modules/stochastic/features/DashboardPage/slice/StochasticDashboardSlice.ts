import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CustomersAverageInvoiceComparisonData,
  FetchDashboardDataRequest,
  FetchDashboardDataResponse,
  LifetimeValueByTierData,
  LifetimeValueDataItem,
  PercentageOfNewCustomersByZipCodeDataItem,
  PercentageOfNewCustomersChangeByZipCodeDataItem,
  TotalSalesByYearAndMonthDataItem,
  TotalSalesByZipCodeDataItem,
  TotalSalesNewCustomerByZipCodeAndYearDataItem,
  TotalSalesNewVsExistingCustomerDataItem,
} from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/types";
import { fetchStochasticDashboardData } from "@/modules/stochastic/features/DashboardPage/api/fetchStochasticDashboardData/fetchStochasticDashboardDataApi";

interface StochasticDashboardState {
  dashboardData: {
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
  loading: boolean;
  error: string | null;
}

const initialState: StochasticDashboardState = {
  dashboardData: {
    lifetimeValueData: [],
    totalSalesByZipCodeData: [],
    totalSalesByYearAndMonthData: [],
    totalSalesNewVsExistingCustomerData: [],
    percentageOfNewCustomersByZipCodeData: [],
    totalSalesNewCustomerByZipCodeAndYearData: [],
    percentageOfNewCustomersChangeByZipCodeData: [],
    customersAverageInvoiceComparisonData: {
      chartData: [],
      avgSales: {
        newCustomerAvg: 0,
        repeatCustomerAvg: 0,
      },
    },
    lifetimeValueByTierData: {
      chartData: [],
      totalHouseholdsCount: 0,
    },
  },
  loading: false,
  error: null,
};

const stochasticDashboardSlice = createSlice({
  name: "stochasticDashboard",
  initialState,
  reducers: {
    setChartsData: (
      state,
      action: PayloadAction<StochasticDashboardState["dashboardData"]>,
    ) => {
      state.dashboardData = action.payload;
    },
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
  },
});

export const { setChartsData, setLoading, setError } =
  stochasticDashboardSlice.actions;

export const fetchStochasticDashboardChartsDataAction =
  (requestData: FetchDashboardDataRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchDashboardDataResponse =
        await fetchStochasticDashboardData(requestData);
      dispatch(setChartsData(response.data));
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch stochastic dashboard data."));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default stochasticDashboardSlice.reducer;
