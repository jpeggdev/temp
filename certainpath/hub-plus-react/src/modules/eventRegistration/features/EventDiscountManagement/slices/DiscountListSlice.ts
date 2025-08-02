import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { Discount } from "@/modules/eventRegistration/features/EventDiscountManagement/api/createDiscount/types";
import { fetchDiscounts } from "@/modules/eventRegistration/features/EventDiscountManagement/api/fetchDiscounts/fetchDiscountsApi";
import {
  FetchDiscountsRequest,
  FetchDiscountsResponse,
} from "@/modules/eventRegistration/features/EventDiscountManagement/api/fetchDiscounts/types";

interface DiscountListState {
  discounts: Discount[];
  totalCount: number | null;
  loading: boolean;
  error: string | null;
}

const initialState: DiscountListState = {
  discounts: [],
  totalCount: null,
  loading: false,
  error: null,
};

const discountListSlice = createSlice({
  name: "discountList",
  initialState,
  reducers: {
    setDiscountData: (
      state,
      action: PayloadAction<{
        discounts: Discount[];
        totalCount: number | null;
      }>,
    ) => {
      state.discounts = action.payload.discounts;
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

export const { setLoading, setError, setDiscountData } =
  discountListSlice.actions;

export const fetchDiscountsAction =
  (requestData: FetchDiscountsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchDiscountsResponse =
        await fetchDiscounts(requestData);
      dispatch(
        setDiscountData({
          discounts: response.data,
          totalCount: response.meta.totalCount,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch discounts."));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default discountListSlice.reducer;
