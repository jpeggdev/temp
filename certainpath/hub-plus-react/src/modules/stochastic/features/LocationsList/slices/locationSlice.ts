import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateLocationRequest,
  CreateLocationResponse,
  Location,
} from "@/modules/stochastic/features/LocationsList/api/createLocation/types";
import { createLocation } from "@/modules/stochastic/features/LocationsList/api/createLocation/createLocationsApi";
import { deleteLocation } from "@/modules/stochastic/features/LocationsList/api/deleteLocation/deleteLocationApi";
import { updateLocation } from "@/modules/stochastic/features/LocationsList/api/updateLocation/updateLocationsApi";
import { getLocation } from "@/modules/stochastic/features/LocationsList/api/getLocation/getLocationsApi";
import {
  UpdateLocationRequest,
  UpdateLocationResponse,
} from "@/modules/stochastic/features/LocationsList/api/updateLocation/types";

interface LocationListState {
  createdLocation: Location | null;
  loadingCreate: boolean;
  errorCreate: string | null;

  fetchedLocation: Location | null;
  loadingGet: boolean;
  errorGet: string | null;

  updatedLocation: Location | null;
  loadingUpdate: boolean;
  errorUpdate: string | null;

  deletedLocationId: number | null;
  loadingDelete: boolean;
  errorDelete: string | null;
}

const initialState: LocationListState = {
  createdLocation: null,
  loadingCreate: false,
  errorCreate: null,

  fetchedLocation: null,
  loadingGet: false,
  errorGet: null,

  updatedLocation: null,
  loadingUpdate: false,
  errorUpdate: null,

  deletedLocationId: null,
  loadingDelete: false,
  errorDelete: null,
};

const locationSlice = createSlice({
  name: "location",
  initialState,
  reducers: {
    setCreatedLocation: (
      state,
      action: PayloadAction<{
        data: Location;
      }>,
    ) => {
      state.createdLocation = action.payload.data;
    },
    setCreateLoading: (state, action: PayloadAction<boolean>) => {
      state.loadingCreate = action.payload;
    },
    setCreateError: (state, action: PayloadAction<string | null>) => {
      state.errorCreate = action.payload;
    },
    setFetchedLocation: (
      state,
      action: PayloadAction<{
        data: Location;
      }>,
    ) => {
      state.fetchedLocation = action.payload.data;
    },
    setLoadingGet: (state, action: PayloadAction<boolean>) => {
      state.loadingGet = action.payload;
    },
    setErrorGet: (state, action: PayloadAction<string | null>) => {
      state.errorGet = action.payload;
    },
    setUpdatedLocation: (
      state,
      action: PayloadAction<{
        data: Location;
      }>,
    ) => {
      state.fetchedLocation = action.payload.data;
    },
    setLoadingUpdate: (state, action: PayloadAction<boolean>) => {
      state.loadingUpdate = action.payload;
    },
    setErrorUpdate: (state, action: PayloadAction<string | null>) => {
      state.errorUpdate = action.payload;
    },
    setDeletedLocationId(state, action: PayloadAction<number | null>) {
      state.deletedLocationId = action.payload;
    },
    setDeleteLoading: (state, action: PayloadAction<boolean>) => {
      state.loadingDelete = action.payload;
    },
    setDeleteError: (state, action: PayloadAction<string | null>) => {
      state.errorDelete = action.payload;
    },
  },
});

export const {
  setCreatedLocation,
  setCreateLoading,
  setCreateError,
  setDeleteLoading,
  setDeleteError,
  setDeletedLocationId,
  setLoadingGet,
  setErrorGet,
  setFetchedLocation,
  setUpdatedLocation,
  setLoadingUpdate,
  setErrorUpdate,
} = locationSlice.actions;

export const createLocationAction =
  (
    requestData: CreateLocationRequest,
    onSuccess?: (createdData: CreateLocationResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setCreateLoading(true));
    dispatch(setCreateError(null));
    try {
      const response = await createLocation(requestData);
      dispatch(setCreatedLocation(response));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to create location.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setCreateLoading(false));
    }
  };

export const getLocationAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingGet(true));
    dispatch(setErrorGet(null));
    try {
      const response = await getLocation(id);
      dispatch(setFetchedLocation(response));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to fetch location.";
      dispatch(setErrorGet(message));
    } finally {
      dispatch(setLoadingGet(false));
    }
  };

export const updateLocationAction =
  (
    id: number,
    requestData: UpdateLocationRequest,
    onSuccess?: (updatedData: UpdateLocationResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setErrorUpdate(null));
    try {
      const response = await updateLocation(id, requestData);
      dispatch(setUpdatedLocation(response));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to update location.";
      dispatch(setErrorUpdate(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const deleteLocationAction =
  (id: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDeleteLoading(true));
    dispatch(setDeleteError(null));
    try {
      await deleteLocation(id);
      dispatch(setDeletedLocationId(id));
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to delete location.";
      dispatch(setDeleteError(message));
      throw error;
    } finally {
      dispatch(setDeleteLoading(false));
    }
  };

export default locationSlice.reducer;
