import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  CreateRestrictedAddressRequest,
  CreateRestrictedAddressResponse,
  RestrictedAddress,
} from "@/api/createRestrictedAddress/types";
import { AppDispatch, AppThunk } from "@/app/store";
import { createRestrictedAddress } from "@/api/createRestrictedAddress/createRestrictedAddressApi";

interface CreateRestrictedAddressState {
  newAddress: RestrictedAddress | null;
  loading: boolean;
  error: string | null;
}

const initialState: CreateRestrictedAddressState = {
  newAddress: null,
  loading: false,
  error: null,
};

const createRestrictedAddressSlice = createSlice({
  name: "createRestrictedAddress",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setNewAddress: (state, action: PayloadAction<RestrictedAddress | null>) => {
      state.newAddress = action.payload;
    },
    resetCreateRestrictedAddress: () => initialState,
  },
});

export const {
  setLoading,
  setError,
  setNewAddress,
  resetCreateRestrictedAddress,
} = createRestrictedAddressSlice.actions;

export const createRestrictedAddressAction =
  (requestData: CreateRestrictedAddressRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    dispatch(setNewAddress(null));
    dispatch(setError(null));

    try {
      const response: CreateRestrictedAddressResponse =
        await createRestrictedAddress(requestData);

      dispatch(setNewAddress(response.data));
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to create restricted address"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default createRestrictedAddressSlice.reducer;
