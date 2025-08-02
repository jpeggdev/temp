import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  Campaign,
  FetchCompanyCampaignsRequest,
  FetchCompanyCampaignsResponse,
} from "../../../../../api/fetchCompanyCampaigns/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { fetchCompanyCampaigns } from "../../../../../api/fetchCompanyCampaigns/fetchCompanyCampaignsApi";

interface CampaignListState {
  campaigns: Campaign[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: CampaignListState = {
  campaigns: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const campaignListSlice = createSlice({
  name: "campaignList",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setCampaignsData: (
      state,
      action: PayloadAction<{ campaigns: Campaign[]; totalCount: number }>,
    ) => {
      state.campaigns = action.payload.campaigns;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setCampaignsData } =
  campaignListSlice.actions;

export const fetchCampaignsAction =
  (requestData: FetchCompanyCampaignsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchCompanyCampaignsResponse =
        await fetchCompanyCampaigns(requestData);
      dispatch(
        setCampaignsData({
          campaigns: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch campaigns"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default campaignListSlice.reducer;
