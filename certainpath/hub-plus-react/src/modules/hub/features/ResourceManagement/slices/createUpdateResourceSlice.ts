import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { createResource } from "@/api/createResource/createResourceApi";
import {
  CreateResourceRequest,
  CreateResourceResponse,
} from "@/api/createResource/types";

import { getResource } from "@/api/getResource/getResourceApi";
import { GetResourceResponse } from "@/api/getResource/types";

import { updateResource } from "@/api/updateResource/updateResourceApi";
import {
  UpdateResourceRequest,
  UpdateResourceResponse,
} from "@/api/updateResource/types";

import { AppDispatch, AppThunk } from "@/app/store";

interface CreateUpdateResourceState {
  loadingCreate: boolean;
  createError: string | null;
  createdResource: CreateResourceResponse["data"] | null;

  loadingGet: boolean;
  getError: string | null;
  fetchedResource: GetResourceResponse["data"] | null;

  loadingUpdate: boolean;
  updateError: string | null;
  updatedResource: UpdateResourceResponse["data"] | null;
}

const initialState: CreateUpdateResourceState = {
  loadingCreate: false,
  createError: null,
  createdResource: null,

  loadingGet: false,
  getError: null,
  fetchedResource: null,

  loadingUpdate: false,
  updateError: null,
  updatedResource: null,
};

const createUpdateResourceSlice = createSlice({
  name: "createUpdateResource",
  initialState,
  reducers: {
    setLoadingCreate(state, action: PayloadAction<boolean>) {
      state.loadingCreate = action.payload;
    },
    setCreateError(state, action: PayloadAction<string | null>) {
      state.createError = action.payload;
    },
    setCreatedResource(
      state,
      action: PayloadAction<CreateResourceResponse["data"] | null>,
    ) {
      state.createdResource = action.payload;
    },
    setLoadingGet(state, action: PayloadAction<boolean>) {
      state.loadingGet = action.payload;
    },
    setGetError(state, action: PayloadAction<string | null>) {
      state.getError = action.payload;
    },
    setFetchedResource(
      state,
      action: PayloadAction<GetResourceResponse["data"] | null>,
    ) {
      state.fetchedResource = action.payload;
    },
    setLoadingUpdate(state, action: PayloadAction<boolean>) {
      state.loadingUpdate = action.payload;
    },
    setUpdateError(state, action: PayloadAction<string | null>) {
      state.updateError = action.payload;
    },
    setUpdatedResource(
      state,
      action: PayloadAction<UpdateResourceResponse["data"] | null>,
    ) {
      state.updatedResource = action.payload;
    },
    resetCreateUpdateResourceState(state) {
      state.loadingCreate = false;
      state.createError = null;
      state.createdResource = null;

      state.loadingGet = false;
      state.getError = null;
      state.fetchedResource = null;

      state.loadingUpdate = false;
      state.updateError = null;
      state.updatedResource = null;
    },
  },
});

export const {
  setLoadingCreate,
  setCreateError,
  setCreatedResource,

  setLoadingGet,
  setGetError,
  setFetchedResource,

  setLoadingUpdate,
  setUpdateError,
  setUpdatedResource,

  resetCreateUpdateResourceState,
} = createUpdateResourceSlice.actions;

export default createUpdateResourceSlice.reducer;

export const createResourceAction =
  (
    requestData: CreateResourceRequest,
    onSuccess?: (resourceData: CreateResourceResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setCreateError(null));
    try {
      const response = await createResource(requestData);
      dispatch(setCreatedResource(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to create the resource.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const getResourceAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingGet(true));
    dispatch(setGetError(null));
    try {
      const response = await getResource(uuid);
      dispatch(setFetchedResource(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch the resource.";
      dispatch(setGetError(message));
    } finally {
      dispatch(setLoadingGet(false));
    }
  };

export const updateResourceAction =
  (
    id: number,
    requestData: UpdateResourceRequest,
    onSuccess?: (updatedData: UpdateResourceResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setUpdateError(null));
    try {
      const response = await updateResource(id, requestData);
      dispatch(setUpdatedResource(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update the resource.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };
