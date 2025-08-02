import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import {
  Prospect,
  GetBatchProspectsRequest,
  GetBatchProspectsResponse,
} from "../../../../../api/getBatchProspects/types";
import { getBatchProspects } from "../../../../../api/getBatchProspects/getBatchProspectsApi";

interface BatchProspectState {
  prospects: Prospect[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: BatchProspectState = {
  prospects: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const batchProspectSlice = createSlice({
  name: "batchProspect",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setProspectsData: (
      state,
      action: PayloadAction<{ prospects: Prospect[]; totalCount: number }>,
    ) => {
      state.prospects = action.payload.prospects;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setProspectsData } =
  batchProspectSlice.actions;

export const fetchBatchProspectsAction =
  (batchId: number, requestData: GetBatchProspectsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: GetBatchProspectsResponse = await getBatchProspects(
        batchId,
        requestData,
      );
      dispatch(
        setProspectsData({
          prospects: response.data,
          totalCount: response.meta.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch batch prospects"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default batchProspectSlice.reducer;
