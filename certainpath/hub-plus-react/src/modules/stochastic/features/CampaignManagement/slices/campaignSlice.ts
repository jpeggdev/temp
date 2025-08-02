import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import {
  Campaign,
  FetchCampaignResponse,
} from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaign/types";
import { fetchCampaign } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaign/fetchCampaignApi";
import {
  UpdateCampaignRequest,
  UpdateCampaignResponse,
} from "../../../../../api/updateCampaign/types";
import { updateCampaign } from "../../../../../api/updateCampaign/updateCampaignApi";
import {
  CampaignStatus,
  FetchCampaignStatusesResponse,
} from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignStatuses/types";
import { fetchCampaignStatuses } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignStatuses/fetchCampaignStatusesApi";

interface CampaignState {
  campaign: Campaign | null;
  statuses: CampaignStatus[];
  loadingFetch: boolean;
  loadingUpdate: boolean;
  errorFetch: string | null;
  errorUpdate: string | null;
  selectedCampaignId: number | null;
}

const initialState: CampaignState = {
  campaign: null,
  statuses: [],
  loadingFetch: false,
  loadingUpdate: false,
  errorFetch: null,
  errorUpdate: null,
  selectedCampaignId: null,
};

const campaignSlice = createSlice({
  name: "campaign",
  initialState,
  reducers: {
    setLoadingFetch: (state, action: PayloadAction<boolean>) => {
      state.loadingFetch = action.payload;
    },
    setLoadingUpdate: (state, action: PayloadAction<boolean>) => {
      state.loadingUpdate = action.payload;
    },
    setErrorFetch: (state, action: PayloadAction<string | null>) => {
      state.errorFetch = action.payload;
    },
    setErrorUpdate: (state, action: PayloadAction<string | null>) => {
      state.errorUpdate = action.payload;
    },
    setCampaignData: (state, action: PayloadAction<Campaign>) => {
      state.campaign = action.payload;
    },
    setCampaignStatuses: (state, action: PayloadAction<CampaignStatus[]>) => {
      state.statuses = action.payload;
    },
    clearCampaignData: (state) => {
      state.campaign = null;
      state.errorFetch = null;
      state.errorUpdate = null;
    },
    setSelectedCampaignId: (state, action: PayloadAction<number | null>) => {
      state.selectedCampaignId = action.payload;
    },
  },
});

export const {
  setLoadingFetch,
  setLoadingUpdate,
  setErrorFetch,
  setErrorUpdate,
  setCampaignData,
  setCampaignStatuses,
  clearCampaignData,
  setSelectedCampaignId,
} = campaignSlice.actions;

export const fetchCampaignAction =
  (campaignId: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingFetch(true));
    dispatch(clearCampaignData());
    try {
      const response: FetchCampaignResponse = await fetchCampaign(campaignId);
      dispatch(setCampaignData(response.data));
      dispatch(setErrorFetch(null));
    } catch (error) {
      dispatch(
        setErrorFetch(
          error instanceof Error ? error.message : "Failed to fetch campaign",
        ),
      );
    } finally {
      dispatch(setLoadingFetch(false));
    }
  };

export const updateCampaignAction =
  (
    campaignId: number,
    requestData: UpdateCampaignRequest,
    callback?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    try {
      const response: UpdateCampaignResponse = await updateCampaign(
        campaignId,
        requestData,
      );
      dispatch(setCampaignData(response.data));
      dispatch(setErrorUpdate(null));
      if (callback) {
        callback();
      }
    } catch (error) {
      dispatch(
        setErrorUpdate(
          error instanceof Error ? error.message : "Failed to update campaign",
        ),
      );
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const fetchCampaignStatusesAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setLoadingFetch(true));
    try {
      const response: FetchCampaignStatusesResponse =
        await fetchCampaignStatuses();
      dispatch(setCampaignStatuses(response.data));
      dispatch(setErrorFetch(null));
    } catch (error) {
      dispatch(
        setErrorFetch(
          error instanceof Error
            ? error.message
            : "Failed to fetch campaign statuses",
        ),
      );
    } finally {
      dispatch(setLoadingFetch(false));
    }
  };

export default campaignSlice.reducer;
