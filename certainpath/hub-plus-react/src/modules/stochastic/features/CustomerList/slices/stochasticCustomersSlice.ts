import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  StochasticCustomer,
  FetchStochasticCustomersRequest,
  FetchStochasticCustomersResponse,
} from "@/api/fetchStochasticCustomers/types";
import { AppDispatch, AppThunk } from "@/app/store";
import { fetchStochasticCustomers } from "@/api/fetchStochasticCustomers/fetchStochasticCustomersApi";
import { updateStochasticCustomerDoNotMail } from "@/api/updateStochasticCustomerDoNotMail/updateStochasticCustomerDoNotMailApi";
import { toast } from "@/components/ui/use-toast";

interface StochasticCustomersState {
  customers: StochasticCustomer[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: StochasticCustomersState = {
  customers: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const stochasticCustomersSlice = createSlice({
  name: "stochasticCustomers",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setCustomersData: (
      state,
      action: PayloadAction<{
        customers: StochasticCustomer[];
        totalCount: number;
      }>,
    ) => {
      state.customers = action.payload.customers;
      state.totalCount = action.payload.totalCount;
    },
    updateCustomerDoNotMail: (
      state,
      action: PayloadAction<{ customerId: number; isDoNotMail: boolean }>,
    ) => {
      const customer = state.customers.find(
        (c) => c.id === action.payload.customerId,
      );
      if (customer) {
        customer.doNotMail = action.payload.isDoNotMail;
      }
    },
    resetStochasticCustomers: () => initialState,
  },
});

export const {
  setLoading,
  setError,
  setCustomersData,
  updateCustomerDoNotMail,
  resetStochasticCustomers,
} = stochasticCustomersSlice.actions;

export const fetchStochasticCustomersAction =
  (requestData: FetchStochasticCustomersRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchStochasticCustomersResponse =
        await fetchStochasticCustomers(requestData);

      dispatch(
        setCustomersData({
          customers: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch customers"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export const updateCustomerDoNotMailAction =
  (customerId: number, isDoNotMail: boolean): AppThunk =>
  async (dispatch: AppDispatch) => {
    // Optimistic update
    dispatch(updateCustomerDoNotMail({ customerId, isDoNotMail }));

    try {
      await updateStochasticCustomerDoNotMail(customerId, {
        doNotMail: isDoNotMail,
      });
      // Keep the optimistic update - no need to update again with response
      toast({
        title: "Success",
        description: "Customer mail preference updated successfully.",
      });
    } catch (error) {
      // Revert on error
      dispatch(
        updateCustomerDoNotMail({ customerId, isDoNotMail: !isDoNotMail }),
      );

      toast({
        title: "Error",
        description: "Failed to update customer mail preference.",
        variant: "destructive",
      });

      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to update Do Not Mail status"));
      }
    }
  };

export default stochasticCustomersSlice.reducer;
