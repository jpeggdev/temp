import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { deleteRestrictedAddress } from "@/api/deleteRestrictedAddress/deleteRestrictedAddressApi";

interface DeleteRestrictedAddressState {
  loading: boolean;
  error: string | null;
  success: boolean;
}

const initialState: DeleteRestrictedAddressState = {
  loading: false,
  error: null,
  success: false,
};

const deleteRestrictedAddressSlice = createSlice({
  name: "deleteRestrictedAddress",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setDeleteSuccess: (state, action: PayloadAction<boolean>) => {
      state.success = action.payload;
    },
  },
});

export const { setLoading, setError, setDeleteSuccess } =
  deleteRestrictedAddressSlice.actions;

export const deleteRestrictedAddressAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    dispatch(setDeleteSuccess(false));
    dispatch(setError(null));

    try {
      await deleteRestrictedAddress(id);

      dispatch(setDeleteSuccess(true));
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to delete restricted address"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default deleteRestrictedAddressSlice.reducer;
