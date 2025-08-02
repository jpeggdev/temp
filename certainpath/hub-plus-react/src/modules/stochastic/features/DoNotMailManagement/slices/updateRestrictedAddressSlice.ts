import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  RestrictedAddress,
  UpdateRestrictedAddressRequest,
  UpdateRestrictedAddressResponse,
} from "@/api/updateRestrictedAddress/types";
import { AppDispatch, AppThunk } from "@/app/store";
import { updateRestrictedAddress } from "@/api/updateRestrictedAddress/updateRestrictedAddressApi";

interface UpdateRestrictedAddressState {
  updatedAddress: RestrictedAddress | null;
  loading: boolean;
  error: string | null;
}

const initialState: UpdateRestrictedAddressState = {
  updatedAddress: null,
  loading: false,
  error: null,
};

const updateRestrictedAddressSlice = createSlice({
  name: "updateRestrictedAddress",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setUpdatedAddress: (
      state,
      action: PayloadAction<RestrictedAddress | null>,
    ) => {
      state.updatedAddress = action.payload;
    },
    resetUpdateRestrictedAddress: () => initialState,
  },
});

export const {
  setLoading,
  setError,
  setUpdatedAddress,
  resetUpdateRestrictedAddress,
} = updateRestrictedAddressSlice.actions;

export const updateRestrictedAddressAction =
  (id: number, requestData: UpdateRestrictedAddressRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    dispatch(setUpdatedAddress(null));
    dispatch(setError(null));

    try {
      const response: UpdateRestrictedAddressResponse =
        await updateRestrictedAddress(id, requestData);

      dispatch(setUpdatedAddress(response.data));
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to update restricted address"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default updateRestrictedAddressSlice.reducer;
