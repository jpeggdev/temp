import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { Voucher } from "@/modules/eventRegistration/features/EventVoucherManagement/api/createVoucher/types";
import {
  FetchVouchersRequest,
  FetchVouchersResponse,
} from "@/modules/eventRegistration/features/EventVoucherManagement/api/fetchVouchers/types";
import { fetchVouchers } from "@/modules/eventRegistration/features/EventVoucherManagement/api/fetchVouchers/fetchVouchersApi";

interface VoucherListState {
  vouchers: Voucher[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: VoucherListState = {
  vouchers: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const voucherListSlice = createSlice({
  name: "voucherList",
  initialState,
  reducers: {
    setVouchersData: (
      state,
      action: PayloadAction<{
        vouchers: Voucher[];
        totalCount: number;
      }>,
    ) => {
      state.vouchers = action.payload.vouchers;
      state.totalCount = action.payload.totalCount;
    },
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
  },
});

export const { setLoading, setError, setVouchersData } =
  voucherListSlice.actions;

export const fetchVouchersAction =
  (requestData: FetchVouchersRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchVouchersResponse = await fetchVouchers(requestData);
      dispatch(
        setVouchersData({
          vouchers: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch vouchers"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default voucherListSlice.reducer;
