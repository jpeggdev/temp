import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateVenueRequest,
  CreateVenueResponse,
  Venue,
} from "@/modules/eventRegistration/features/EventVenueManagement/api/createVenue/types";
import { fetchVenue } from "@/modules/eventRegistration/features/EventVenueManagement/api/fetchVenue/fetchVenueApi";
import { createVenue } from "@/modules/eventRegistration/features/EventVenueManagement/api/createVenue/createVenueApi";
import { deleteVenue } from "@/modules/eventRegistration/features/EventVenueManagement/api/deleteVenue/deleteVenueApi";
import {
  EditVenueRequest,
  EditVenueResponse,
} from "@/modules/eventRegistration/features/EventVenueManagement/api/editVenue/types";
import { updateVenue } from "@/modules/eventRegistration/features/EventVenueManagement/api/editVenue/editVenueApi";

interface VenueState {
  createdVenue: Venue | null;
  loadingCreate: boolean;
  errorCreate: string | null;

  updatedVenue: Venue | null;
  loadingUpdate: boolean;
  errorUpdate: string | null;

  fetchedVenue: Venue | null;
  loadingFetch: boolean;
  errorFetch: string | null;

  loadingDelete: boolean;
  errorDelete: string | null;
}

const initialState: VenueState = {
  createdVenue: null,
  loadingCreate: false,
  errorCreate: null,

  updatedVenue: null,
  loadingUpdate: false,
  errorUpdate: null,

  fetchedVenue: null,
  loadingFetch: false,
  errorFetch: null,

  loadingDelete: false,
  errorDelete: null,
};

const venueSlice = createSlice({
  name: "venue",
  initialState,
  reducers: {
    setCreatedVenue: (
      state,
      action: PayloadAction<{
        data: Venue;
      }>,
    ) => {
      state.createdVenue = action.payload.data;
    },
    setLoadingCreate: (state, action: PayloadAction<boolean>) => {
      state.loadingCreate = action.payload;
    },
    setErrorCreate: (state, action: PayloadAction<string | null>) => {
      state.errorCreate = action.payload;
    },
    setUpdatedVenue: (
      state,
      action: PayloadAction<{
        data: Venue;
      }>,
    ) => {
      state.updatedVenue = action.payload.data;
    },
    setLoadingUpdate: (state, action: PayloadAction<boolean>) => {
      state.loadingUpdate = action.payload;
    },
    setErrorUpdate: (state, action: PayloadAction<string | null>) => {
      state.errorUpdate = action.payload;
    },
    setFetchedVenue: (
      state,
      action: PayloadAction<{
        data: Venue;
      }>,
    ) => {
      state.fetchedVenue = action.payload.data;
    },
    setLoadingFetch: (state, action: PayloadAction<boolean>) => {
      state.loadingFetch = action.payload;
    },
    setErrorFetch: (state, action: PayloadAction<string | null>) => {
      state.errorFetch = action.payload;
    },
    setLoadingDelete: (state, action: PayloadAction<boolean>) => {
      state.loadingDelete = action.payload;
    },
    setErrorDelete: (state, action: PayloadAction<string | null>) => {
      state.errorDelete = action.payload;
    },
  },
});

export const {
  setLoadingCreate,
  setErrorCreate,
  setCreatedVenue,
  setLoadingDelete,
  setErrorDelete,
  setFetchedVenue,
  setUpdatedVenue,
  setLoadingUpdate,
  setErrorUpdate,
  setLoadingFetch,
  setErrorFetch,
} = venueSlice.actions;

export const createVenueAction =
  (
    requestData: CreateVenueRequest,
    onSuccess?: (createdData: CreateVenueResponse["data"]) => void,
  ) =>
  async (dispatch: AppDispatch): Promise<void> => {
    dispatch(setLoadingCreate(true));
    dispatch(setErrorCreate(null));

    try {
      try {
        const response = await createVenue(requestData);
        dispatch(setCreatedVenue(response));
        if (onSuccess) {
          onSuccess(response.data);
        }
      } catch (error) {
        const message =
          error instanceof Error
            ? error.message
            : "Failed to create the venue.";
        dispatch(setErrorCreate(message));
        throw error;
      }
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const fetchVenueAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingFetch(true));
    try {
      const response = await fetchVenue(id);
      dispatch(setFetchedVenue(response));
    } catch (error) {
      dispatch(
        setErrorFetch(
          error instanceof Error ? error.message : "Failed to fetch the venue.",
        ),
      );
    } finally {
      dispatch(setLoadingFetch(false));
    }
  };

export const updateVenueAction =
  (
    id: number,
    requestData: EditVenueRequest,
    onSuccess?: (updatedData: EditVenueResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setErrorUpdate(null));
    try {
      const response = await updateVenue(id, requestData);
      dispatch(setUpdatedVenue(response));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to update the venue.";
      dispatch(setErrorUpdate(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const deleteVenueAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDelete(true));
    try {
      await deleteVenue(id);
    } catch (error) {
      dispatch(
        setErrorDelete(
          error instanceof Error
            ? error.message
            : "Failed to delete the venue.",
        ),
      );
    } finally {
      dispatch(setLoadingDelete(false));
    }
  };

export default venueSlice.reducer;
