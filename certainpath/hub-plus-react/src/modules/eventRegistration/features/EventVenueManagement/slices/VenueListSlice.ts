import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { Venue } from "@/modules/eventRegistration/features/EventVenueManagement/api/createVenue/types";
import { fetchVenues } from "@/modules/eventRegistration/features/EventVenueManagement/api/fetchVenues/fetchVenuesApi";
import {
  FetchVenuesRequest,
  FetchVenuesResponse,
} from "@/modules/eventRegistration/features/EventVenueManagement/api/fetchVenues/types";

interface VenueListState {
  venues: Venue[];
  totalCount: number;
  loading: boolean;
  error: string | null;
}

const initialState: VenueListState = {
  venues: [],
  totalCount: 0,
  loading: false,
  error: null,
};

const venueListSlice = createSlice({
  name: "venueList",
  initialState,
  reducers: {
    setVenuesData: (
      state,
      action: PayloadAction<{
        venues: Venue[];
        totalCount: number;
      }>,
    ) => {
      state.venues = action.payload.venues;
      state.totalCount = action.payload.totalCount;
    },
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    },
  },
});

export const { setLoading, setError, setVenuesData } = venueListSlice.actions;

export const fetchVenuesAction =
  (requestData: FetchVenuesRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoading(true));
    try {
      const response: FetchVenuesResponse = await fetchVenues(requestData);
      dispatch(
        setVenuesData({
          venues: response.data,
          totalCount: response.meta?.totalCount || 0,
        }),
      );
    } catch (error) {
      if (error instanceof Error) {
        dispatch(setError(error.message));
      } else {
        dispatch(setError("Failed to fetch venues"));
      }
    } finally {
      dispatch(setLoading(false));
    }
  };

export default venueListSlice.reducer;
