import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  EventSearchFilters,
  FetchEventSearchResultsRequest,
  FetchEventSearchResultsResponse,
  SearchResultEvent,
} from "@/modules/eventRegistration/features/EventDirectory/api/fetchEventSearchResults/types";
import {
  EventDetails,
  FetchEventDetailsRequest,
  FetchEventDetailsResponse,
} from "@/modules/eventRegistration/features/EventDirectory/api/fetchEventDetails/types";
import { fetchEventSearchResults } from "@/modules/eventRegistration/features/EventDirectory/api/fetchEventSearchResults/fetchEventSearchResultsApi";
import { fetchEventDetails } from "@/modules/eventRegistration/features/EventDirectory/api/fetchEventDetails/fetchEventDetailsApi";
import { ToggleFavoriteEventResponse } from "@/modules/eventRegistration/features/EventDirectory/api/toggleFavoriteEvent/types";
import { toggleFavoriteEvent } from "@/modules/eventRegistration/features/EventDirectory/api/toggleFavoriteEvent/toggleFavoriteEventApi";

interface EventDirectoryState {
  searchData: SearchResultEvent[];
  searchFilters: EventSearchFilters;
  searchTotalCount: number;
  fetchSearchLoading: boolean;
  fetchSearchError: string | null;
  eventDetails: EventDetails | null;
  fetchDetailsLoading: boolean;
  fetchDetailsError: string | null;
  toggleFavoriteLoading: boolean;
  toggleFavoriteError: string | null;
}

const initialState: EventDirectoryState = {
  searchData: [],
  searchFilters: {
    eventTypes: [],
    categories: [],
    trades: [],
    employeeRoles: [],
  },
  searchTotalCount: 0,
  fetchSearchLoading: false,
  fetchSearchError: null,
  eventDetails: null,
  fetchDetailsLoading: false,
  fetchDetailsError: null,
  toggleFavoriteLoading: false,
  toggleFavoriteError: null,
};

const eventDirectorySlice = createSlice({
  name: "eventDirectory",
  initialState,
  reducers: {
    setFetchSearchLoading(state, action: PayloadAction<boolean>) {
      state.fetchSearchLoading = action.payload;
    },
    setFetchSearchError(state, action: PayloadAction<string | null>) {
      state.fetchSearchError = action.payload;
    },
    setSearchData(state, action: PayloadAction<SearchResultEvent[]>) {
      state.searchData = action.payload;
    },
    appendSearchData(state, action: PayloadAction<SearchResultEvent[]>) {
      state.searchData = [...state.searchData, ...action.payload];
    },
    setSearchFilters(
      state,
      action: PayloadAction<EventDirectoryState["searchFilters"]>,
    ) {
      state.searchFilters = action.payload;
    },
    setSearchTotalCount(state, action: PayloadAction<number>) {
      state.searchTotalCount = action.payload;
    },
    setFetchDetailsLoading(state, action: PayloadAction<boolean>) {
      state.fetchDetailsLoading = action.payload;
    },
    setFetchDetailsError(state, action: PayloadAction<string | null>) {
      state.fetchDetailsError = action.payload;
    },
    setEventDetails(state, action: PayloadAction<EventDetails | null>) {
      state.eventDetails = action.payload;
    },
    setToggleFavoriteLoading(state, action: PayloadAction<boolean>) {
      state.toggleFavoriteLoading = action.payload;
    },
    setToggleFavoriteError(state, action: PayloadAction<string | null>) {
      state.toggleFavoriteError = action.payload;
    },
    updateFavoriteStatus(
      state,
      action: PayloadAction<{ uuid: string; favorited: boolean }>,
    ) {
      const { uuid, favorited } = action.payload;
      if (state.eventDetails && state.eventDetails.uuid === uuid) {
        state.eventDetails.isFavorited = favorited;
      }
    },
  },
});

export default eventDirectorySlice.reducer;

export const {
  setFetchSearchLoading,
  setFetchSearchError,
  setSearchData,
  appendSearchData,
  setSearchFilters,
  setSearchTotalCount,
  setFetchDetailsLoading,
  setFetchDetailsError,
  setEventDetails,
  setToggleFavoriteLoading,
  setToggleFavoriteError,
  updateFavoriteStatus,
} = eventDirectorySlice.actions;

export const fetchEventSearchResultsAction =
  (params: FetchEventSearchResultsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    if (!params.page || params.page === 1) {
      dispatch(setFetchSearchLoading(true));
    }
    dispatch(setFetchSearchError(null));
    try {
      const response: FetchEventSearchResultsResponse =
        await fetchEventSearchResults(params);

      if (params.page && params.page > 1) {
        dispatch(appendSearchData(response.data.events));
      } else {
        dispatch(setSearchData(response.data.events));
      }

      dispatch(setSearchFilters(response.data.filters));
      dispatch(setSearchTotalCount(response.meta?.totalCount ?? 0));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event search results.";
      dispatch(setFetchSearchError(message));
    } finally {
      dispatch(setFetchSearchLoading(false));
    }
  };

export const fetchEventDetailsAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setFetchDetailsLoading(true));
    dispatch(setFetchDetailsError(null));
    try {
      const requestData: FetchEventDetailsRequest = { uuid };
      const response: FetchEventDetailsResponse =
        await fetchEventDetails(requestData);
      dispatch(setEventDetails(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event details.";
      dispatch(setFetchDetailsError(message));
    } finally {
      dispatch(setFetchDetailsLoading(false));
    }
  };

export const toggleFavoriteEventAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setToggleFavoriteLoading(true));
    dispatch(setToggleFavoriteError(null));
    try {
      const response: ToggleFavoriteEventResponse =
        await toggleFavoriteEvent(uuid);
      dispatch(
        updateFavoriteStatus({ uuid, favorited: response.data.favorited }),
      );
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to toggle favorite.";
      dispatch(setToggleFavoriteError(message));
    } finally {
      dispatch(setToggleFavoriteLoading(false));
    }
  };
