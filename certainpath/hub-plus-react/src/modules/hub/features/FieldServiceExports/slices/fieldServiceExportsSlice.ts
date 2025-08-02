import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  FetchFieldServiceExportsRequest,
  FieldServiceExport,
} from "../../../../../api/fetchFieldServiceExports/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { fetchFieldServiceExports } from "../../../../../api/fetchFieldServiceExports/fetchFieldServiceExportsApi";

interface FieldServiceExportsState {
  exports: FieldServiceExport[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: FieldServiceExportsState = {
  exports: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const fieldServiceExportsSlice = createSlice({
  name: "fieldServiceExports",
  initialState,
  reducers: {
    setLoading(state, action: PayloadAction<boolean>) {
      state.loading = action.payload;
    },
    setError(state, action: PayloadAction<string | null>) {
      state.error = action.payload;
    },
    setExports(
      state,
      action: PayloadAction<{
        exports: FieldServiceExport[];
        totalCount: number;
      }>,
    ) {
      state.exports = action.payload.exports;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setExports } =
  fieldServiceExportsSlice.actions;

// Thunk action to fetch field service exports
export const fetchFieldServiceExportsAction =
  (requestData: FetchFieldServiceExportsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response = await fetchFieldServiceExports(requestData);
      dispatch(
        setExports({
          exports: response.data.exports,
          totalCount: response.meta?.totalCount ?? 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch field service exports"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default fieldServiceExportsSlice.reducer;
