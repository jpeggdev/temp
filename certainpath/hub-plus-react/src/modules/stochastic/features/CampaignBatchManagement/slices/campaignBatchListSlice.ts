// src/modules/stochastic/features/CampaignBatchManagement/slices/campaignBatchListSlice.ts

import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  Batch,
  GetCampaignBatchesRequest,
  GetCampaignBatchesResponse,
} from "../../../../../api/getCampaignBatches/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { getCampaignBatches } from "../../../../../api/getCampaignBatches/getCampaignBatchesApi";

interface CampaignBatchListState {
  batches: Batch[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: CampaignBatchListState = {
  batches: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const campaignBatchListSlice = createSlice({
  name: "campaignBatchList",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setBatchesData: (
      state,
      action: PayloadAction<{ batches: Batch[]; totalCount: number }>,
    ) => {
      state.batches = action.payload.batches;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setBatchesData } =
  campaignBatchListSlice.actions;

export const fetchCampaignBatchesAction =
  (campaignId: number, requestData: GetCampaignBatchesRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: GetCampaignBatchesResponse = await getCampaignBatches(
        campaignId,
        requestData,
      );
      dispatch(
        setBatchesData({
          batches: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch campaign batches"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default campaignBatchListSlice.reducer;
