import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { CreateCampaignRequest } from "@/modules/stochastic/features/CampaignManagement/api/createCampaign/types";
import { AppDispatch, AppThunk } from "../../../../../app/store";
import { createCampaign } from "@/modules/stochastic/features/CampaignManagement/api/createCampaign/createCampaignApi";
import {
  CampaignStatus,
  FetchCampaignStatusesResponse,
} from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignStatuses/types";
import { fetchCampaignStatuses } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignStatuses/fetchCampaignStatusesApi";
import { fetchAggregatedProspects } from "../../../../../api/fetchAggregatedProspects/fetchAggregatedProspectsApi";
import {
  AggregatedProspect,
  FetchAggregatedProspectsRequest,
  FetchAggregatedProspectsResponse,
} from "../../../../../api/fetchAggregatedProspects/types";

import { fetchCampaignDetailsMetadata } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignDetailsMetadata/fetchCampaignDetailsMetadataApi";
import {
  CampaignDetailsMetadata,
  FetchCampaignDetailsMetadataResponse,
} from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignDetailsMetadata/types";

interface CreateCampaignState {
  statuses: CampaignStatus[];
  loadingCreate: boolean;
  loadingStatuses: boolean;
  errorCreate: string | null;
  errorStatuses: string | null;
  aggregatedProspects: AggregatedProspect[];
  loadingAggregatedProspects: boolean;
  errorAggregatedProspects: string | null;
  campaignDetailsMetadata: CampaignDetailsMetadata | null;
  loadingCampaignDetailsMetadata: boolean;
  errorCampaignDetailsMetadata: string | null;
}

const initialState: CreateCampaignState = {
  statuses: [],
  loadingCreate: false,
  loadingStatuses: false,
  errorCreate: null,
  errorStatuses: null,
  aggregatedProspects: [],
  loadingAggregatedProspects: false,
  errorAggregatedProspects: null,
  campaignDetailsMetadata: null,
  loadingCampaignDetailsMetadata: false,
  errorCampaignDetailsMetadata: null,
};

const createCampaignSlice = createSlice({
  name: "createCampaign",
  initialState,
  reducers: {
    setLoadingCreate: (state, action: PayloadAction<boolean>) => {
      state.loadingCreate = action.payload;
    },
    setLoadingStatuses: (state, action: PayloadAction<boolean>) => {
      state.loadingStatuses = action.payload;
    },
    setErrorCreate: (state, action: PayloadAction<string | null>) => {
      state.errorCreate = action.payload;
    },
    setErrorStatuses: (state, action: PayloadAction<string | null>) => {
      state.errorStatuses = action.payload;
    },
    setCampaignStatuses: (state, action: PayloadAction<CampaignStatus[]>) => {
      state.statuses = action.payload;
    },
    clearCreateCampaignData: (state) => {
      state.errorCreate = null;
    },
    setLoadingAggregatedProspects: (state, action: PayloadAction<boolean>) => {
      state.loadingAggregatedProspects = action.payload;
    },
    setErrorAggregatedProspects: (
      state,
      action: PayloadAction<string | null>,
    ) => {
      state.errorAggregatedProspects = action.payload;
    },
    setAggregatedProspects: (
      state,
      action: PayloadAction<AggregatedProspect[]>,
    ) => {
      state.aggregatedProspects = action.payload;
    },
    setLoadingCampaignDetailsMetadata: (
      state,
      action: PayloadAction<boolean>,
    ) => {
      state.loadingCampaignDetailsMetadata = action.payload;
    },
    setErrorCampaignDetailsMetadata: (
      state,
      action: PayloadAction<string | null>,
    ) => {
      state.errorCampaignDetailsMetadata = action.payload;
    },
    setCampaignDetailsMetadata: (
      state,
      action: PayloadAction<CampaignDetailsMetadata>,
    ) => {
      state.campaignDetailsMetadata = action.payload;
    },
  },
});

export const {
  setLoadingCreate,
  setLoadingStatuses,
  setErrorCreate,
  setErrorStatuses,
  setCampaignStatuses,
  clearCreateCampaignData,
  setLoadingAggregatedProspects,
  setErrorAggregatedProspects,
  setAggregatedProspects,
  setLoadingCampaignDetailsMetadata,
  setErrorCampaignDetailsMetadata,
  setCampaignDetailsMetadata,
} = createCampaignSlice.actions;

export const createCampaignAction =
  (requestData: CreateCampaignRequest, callback?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    try {
      await createCampaign(requestData);
      dispatch(setErrorCreate(null));
      if (callback) {
        callback();
      }
    } catch (error) {
      dispatch(
        setErrorCreate(
          error instanceof Error ? error.message : "Failed to create campaign",
        ),
      );
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const fetchCampaignStatusesAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setLoadingStatuses(true));
    try {
      const response: FetchCampaignStatusesResponse =
        await fetchCampaignStatuses();
      dispatch(setCampaignStatuses(response.data));
      dispatch(setErrorStatuses(null));
    } catch (error) {
      dispatch(
        setErrorStatuses(
          error instanceof Error
            ? error.message
            : "Failed to fetch campaign statuses",
        ),
      );
    } finally {
      dispatch(setLoadingStatuses(false));
    }
  };

export const fetchAggregatedProspectsAction =
  (requestParams: FetchAggregatedProspectsRequest = {}): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingAggregatedProspects(true));
    try {
      const response: FetchAggregatedProspectsResponse =
        await fetchAggregatedProspects(requestParams);
      // response.data is an array of AggregatedProspect
      dispatch(setAggregatedProspects(response.data));
      dispatch(setErrorAggregatedProspects(null));
    } catch (error) {
      dispatch(
        setErrorAggregatedProspects(
          error instanceof Error
            ? error.message
            : "Failed to fetch aggregated prospects",
        ),
      );
    } finally {
      dispatch(setLoadingAggregatedProspects(false));
    }
  };

export const fetchCampaignDetailsMetadataAction =
  (): AppThunk => async (dispatch: AppDispatch) => {
    dispatch(setLoadingCampaignDetailsMetadata(true));
    try {
      const response: FetchCampaignDetailsMetadataResponse =
        await fetchCampaignDetailsMetadata();
      dispatch(setCampaignDetailsMetadata(response.data));
      dispatch(setErrorCampaignDetailsMetadata(null));
    } catch (error) {
      dispatch(
        setErrorCampaignDetailsMetadata(
          error instanceof Error
            ? error.message
            : "Failed to fetch campaign details metadata",
        ),
      );
    } finally {
      dispatch(setLoadingCampaignDetailsMetadata(false));
    }
  };

export default createCampaignSlice.reducer;
