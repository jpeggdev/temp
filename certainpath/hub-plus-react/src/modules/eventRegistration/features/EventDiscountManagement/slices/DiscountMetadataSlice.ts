import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { DiscountMetadata } from "@/modules/eventRegistration/features/EventDiscountManagement/api/fetchEventDiscountMetadata/types";
import { fetchDiscountMetadata } from "@/modules/eventRegistration/features/EventDiscountManagement/api/fetchEventDiscountMetadata/fetchEventDiscountMetadataApi";

interface DiscountMetadataState {
  discountMetadata: DiscountMetadata | null;
  loadingFetch: boolean;
  errorFetch: string | null;
}

const initialState: DiscountMetadataState = {
  discountMetadata: null,
  loadingFetch: false,
  errorFetch: null,
};

const discountMetadataSlice = createSlice({
  name: "discountMetadata",
  initialState,
  reducers: {
    setDiscountMetadata: (
      state,
      action: PayloadAction<{
        data: DiscountMetadata;
      }>,
    ) => {
      state.discountMetadata = action.payload.data;
    },
    setLoadingFetch: (state, action: PayloadAction<boolean>) => {
      state.loadingFetch = action.payload;
    },
    setErrorFetch: (state, action: PayloadAction<string | null>) => {
      state.errorFetch = action.payload;
    },
  },
});

export const { setDiscountMetadata, setLoadingFetch, setErrorFetch } =
  discountMetadataSlice.actions;

export const fetchDiscountMetadataAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setLoadingFetch(true));
    try {
      const response = await fetchDiscountMetadata();
      dispatch(setDiscountMetadata(response));
    } catch (error) {
      dispatch(
        setErrorFetch(
          error instanceof Error
            ? error.message
            : "Failed to fetch the discount metadata.",
        ),
      );
    } finally {
      dispatch(setLoadingFetch(false));
    }
  };

export default discountMetadataSlice.reducer;
