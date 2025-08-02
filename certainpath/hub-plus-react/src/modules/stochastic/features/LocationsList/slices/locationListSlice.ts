import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  FetchLocationsRequest,
  FetchLocationsResponse,
} from "@/modules/stochastic/features/LocationsList/api/fetchLocations/types";
import { fetchLocations } from "@/modules/stochastic/features/LocationsList/api/fetchLocations/fetchLocationsApi";
import { Location } from "@/modules/stochastic/features/LocationsList/api/createLocation/types";

interface LocationListState {
  locations: Location[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: LocationListState = {
  locations: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const locationListSlice = createSlice({
  name: "locationList",
  initialState,
  reducers: {
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
    setLocationsData: (
      state,
      action: PayloadAction<{
        locations: Location[];
        totalCount: number;
      }>,
    ) => {
      state.locations = action.payload.locations;
      state.totalCount = action.payload.totalCount;
    },
  },
});

export const { setLoading, setError, setLocationsData } =
  locationListSlice.actions;

export const fetchLocationsAction =
  (requestData: FetchLocationsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchLocationsResponse =
        await fetchLocations(requestData);
      dispatch(
        setLocationsData({
          locations: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch locations"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default locationListSlice.reducer;
