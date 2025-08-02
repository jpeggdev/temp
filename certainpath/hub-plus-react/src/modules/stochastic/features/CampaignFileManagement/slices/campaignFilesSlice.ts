import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  CampaignFile,
  FetchCampaignFilesRequest,
  FetchCampaignFilesResponse,
} from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignFiles/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { fetchCampaignFiles } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignFiles/fetchCampaignFilesApi";

interface CampaignFilesState {
  files: CampaignFile[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: CampaignFilesState = {
  files: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const campaignFilesSlice = createSlice({
  name: "campaignFiles",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setFilesData: (
      state,
      action: PayloadAction<{ files: CampaignFile[]; totalCount: number }>,
    ) => {
      state.files = action.payload.files;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setFilesData } =
  campaignFilesSlice.actions;

export const fetchCampaignFilesAction =
  (campaignId: number, requestData: FetchCampaignFilesRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchCampaignFilesResponse = await fetchCampaignFiles(
        campaignId,
        requestData,
      );
      dispatch(
        setFilesData({
          files: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch campaign files"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default campaignFilesSlice.reducer;
