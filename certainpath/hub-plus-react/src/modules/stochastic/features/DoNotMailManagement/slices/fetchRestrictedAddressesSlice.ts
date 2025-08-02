import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  FetchRestrictedAddressesRequest,
  FetchRestrictedAddressesResponse,
  RestrictedAddress,
} from "@/api/fetchRestrictedAddresses/types";
import { AppDispatch, AppThunk } from "@/app/store";
import { fetchRestrictedAddresses } from "@/api/fetchRestrictedAddresses/fetchRestrictedAddressesApi";

interface FetchRestrictedAddressesState {
  addresses: RestrictedAddress[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: FetchRestrictedAddressesState = {
  addresses: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const fetchRestrictedAddressesSlice = createSlice({
  name: "fetchRestrictedAddresses",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setAddressesData: (
      state,
      action: PayloadAction<{
        addresses: RestrictedAddress[];
        totalCount: number;
      }>,
    ) => {
      state.addresses = action.payload.addresses;
      state.totalCount = action.payload.totalCount;
    },
    resetFetchRestrictedAddresses: () => initialState,
  },
});

export const {
  setLoading,
  setError,
  setAddressesData,
  resetFetchRestrictedAddresses,
} = fetchRestrictedAddressesSlice.actions;

export const fetchRestrictedAddressesAction =
  (requestData: FetchRestrictedAddressesRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchRestrictedAddressesResponse =
        await fetchRestrictedAddresses(requestData);

      dispatch(
        setAddressesData({
          addresses: response.data,
          totalCount: response.meta.totalCount ?? 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch restricted addresses"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default fetchRestrictedAddressesSlice.reducer;
