import { AppDispatch, AppThunk } from "@/app/store";
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  BulkBatchStatusDetailsMetadata,
  FetchBulkBatchStatusDetailsMetadataResponse,
} from "@/api/fetchBulkBatchStatusDetailsMetadata/types";
import { fetchBulkBatchStatusDetailsMetadata } from "@/api/fetchBulkBatchStatusDetailsMetadata/fetchBulkBatchStatusDetailsMetadataApi";
import { FetchStochasticClientMailDataRequest } from "@/api/fetchStochasticClientMailData/types";

interface BulkBatchStatusDetailsMetadataState {
  bulkBatchStatusDetailsMetadata: BulkBatchStatusDetailsMetadata;
  loading: boolean;
  error: string | null;
}

const initialState: BulkBatchStatusDetailsMetadataState = {
  bulkBatchStatusDetailsMetadata: {
    currentStatus: "",
    bulkBatchStatusOptions: [],
  },
  loading: false,
  error: null,
};

const bulkBatchStatusDetailsMetadataSlice = createSlice({
  name: "bulkBatchStatusDetailsMetadata",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setBulkBatchStatusDetailsMetadata: (
      state,
      action: PayloadAction<BulkBatchStatusDetailsMetadata>,
    ) => {
      state.bulkBatchStatusDetailsMetadata = action.payload;
    },
  },
});

export const { setLoading, setError, setBulkBatchStatusDetailsMetadata } =
  bulkBatchStatusDetailsMetadataSlice.actions;

export const fetchBulkBatchStatusDetailsMetadataAction =
  (requestParams: FetchStochasticClientMailDataRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchBulkBatchStatusDetailsMetadataResponse =
        await fetchBulkBatchStatusDetailsMetadata(requestParams);
      dispatch(setBulkBatchStatusDetailsMetadata(response.data));
      dispatch(setError(null));
    } catch (error) {
      dispatch(
        setError(
          error instanceof Error
            ? error.message
            : "Failed to fetch bulk batch status details metadata",
        ),
      );
    } finally {
      dispatch(setLoading(false));
    }
  };

export default bulkBatchStatusDetailsMetadataSlice.reducer;
