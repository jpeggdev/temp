import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  RestrictedAddress,
  FetchSingleRestrictedAddressResponse,
} from "@/api/fetchSingleRestrictedAddress/types";
import { fetchSingleRestrictedAddressById } from "@/api/fetchSingleRestrictedAddress/fetchSingleRestrictedAddressApi";

interface FetchSingleRestrictedAddressState {
  restrictedAddress: RestrictedAddress | null;
  loading: boolean;
  error: string | null;
}

const initialState: FetchSingleRestrictedAddressState = {
  restrictedAddress: null,
  loading: false,
  error: null,
};

const fetchSingleRestrictedAddressSlice = createSlice({
  name: "fetchSingleRestrictedAddress",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setRestrictedAddress: (
      state,
      action: PayloadAction<RestrictedAddress | null>,
    ) => {
      state.restrictedAddress = action.payload;
    },
    resetFetchSingleRestrictedAddress: () => initialState,
  },
});

export const {
  setLoading,
  setError,
  setRestrictedAddress,
  resetFetchSingleRestrictedAddress,
} = fetchSingleRestrictedAddressSlice.actions;

export const fetchSingleRestrictedAddressAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    dispatch(setRestrictedAddress(null));
    try {
      const response: FetchSingleRestrictedAddressResponse =
        await fetchSingleRestrictedAddressById(id);

      dispatch(setRestrictedAddress(response.data));
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch restricted address"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default fetchSingleRestrictedAddressSlice.reducer;
