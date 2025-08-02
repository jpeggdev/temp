import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import {
  FetchStochasticProspectsRequest,
  FetchStochasticProspectsResponse,
  StochasticProspect,
} from "@/api/fetchStochasticProspects/types";
import { AppDispatch, AppThunk } from "@/app/store";
import { fetchStochasticProspects } from "@/api/fetchStochasticProspects/fetchStochasticProspectsApi";

interface StochasticProspectsState {
  prospects: StochasticProspect[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: StochasticProspectsState = {
  prospects: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const stochasticProspectsSlice = createSlice({
  name: "stochasticProspects",
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
      action: PayloadAction<{
        prospects: StochasticProspect[];
        totalCount: number;
      }>,
    ) => {
      state.prospects = action.payload.prospects;
      state.totalCount = action.payload.totalCount;
    },
    updateProspectDoNotMail: (
      state,
      action: PayloadAction<{ prospectId: number; doNotMail: boolean }>,
    ) => {
      const prospect = state.prospects.find(
        (p) => p.id === action.payload.prospectId,
      );
      if (prospect) {
        prospect.doNotMail = action.payload.doNotMail;
      }
    },
    resetStochasticProspects: () => initialState,
  },
});

export const {
  setLoading,
  setError,
  setProspectsData,
  updateProspectDoNotMail,
  resetStochasticProspects,
} = stochasticProspectsSlice.actions;

export const fetchStochasticProspectsAction =
  (requestData: FetchStochasticProspectsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchStochasticProspectsResponse =
        await fetchStochasticProspects(requestData);
      dispatch(
        setProspectsData({
          prospects: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch stochastic prospects"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default stochasticProspectsSlice.reducer;
