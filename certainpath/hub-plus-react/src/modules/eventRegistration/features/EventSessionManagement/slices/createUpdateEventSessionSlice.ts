import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { createEventSession } from "@/modules/eventRegistration/features/EventSessionManagement/api/createEventSession/createEventSessionApi";
import {
  CreatedEventSessionData,
  CreateEventSessionRequest,
  CreateEventSessionResponse,
} from "@/modules/eventRegistration/features/EventSessionManagement/api/createEventSession/types";
import { updateEventSession } from "@/modules/eventRegistration/features/EventSessionManagement/api/updateEventSession/updateEventSessionApi";
import {
  UpdatedEventSessionData,
  UpdateEventSessionRequest,
  UpdateEventSessionResponse,
} from "@/modules/eventRegistration/features/EventSessionManagement/api/updateEventSession/types";
import { SingleEventSession } from "@/modules/eventRegistration/features/EventSessionManagement/api/getSingleEventSession/types";
import { fetchSingleEventSession } from "@/modules/eventRegistration/features/EventSessionManagement/api/getSingleEventSession/getSingleEventSessionApi";

interface CreateUpdateEventSessionState {
  loadingCreate: boolean;
  createError: string | null;
  createdSession: CreatedEventSessionData | null;

  loadingUpdate: boolean;
  updateError: string | null;
  updatedSession: UpdatedEventSessionData | null;

  loadingFetchSingle: boolean;
  fetchSingleError: string | null;
  singleSession: SingleEventSession | null;
}

const initialState: CreateUpdateEventSessionState = {
  loadingCreate: false,
  createError: null,
  createdSession: null,

  loadingUpdate: false,
  updateError: null,
  updatedSession: null,

  loadingFetchSingle: false,
  fetchSingleError: null,
  singleSession: null,
};

const createUpdateEventSessionSlice = createSlice({
  name: "createUpdateEventSession",
  initialState,
  reducers: {
    setLoadingCreate(state, action: PayloadAction<boolean>) {
      state.loadingCreate = action.payload;
    },
    setCreateError(state, action: PayloadAction<string | null>) {
      state.createError = action.payload;
    },
    setCreatedSession(
      state,
      action: PayloadAction<CreatedEventSessionData | null>,
    ) {
      state.createdSession = action.payload;
    },

    setLoadingUpdate(state, action: PayloadAction<boolean>) {
      state.loadingUpdate = action.payload;
    },
    setUpdateError(state, action: PayloadAction<string | null>) {
      state.updateError = action.payload;
    },
    setUpdatedSession(
      state,
      action: PayloadAction<UpdatedEventSessionData | null>,
    ) {
      state.updatedSession = action.payload;
    },

    setLoadingFetchSingle(state, action: PayloadAction<boolean>) {
      state.loadingFetchSingle = action.payload;
    },
    setFetchSingleError(state, action: PayloadAction<string | null>) {
      state.fetchSingleError = action.payload;
    },
    setSingleSession(state, action: PayloadAction<SingleEventSession | null>) {
      state.singleSession = action.payload;
    },

    resetCreateUpdateEventSessionState(state) {
      state.loadingCreate = false;
      state.createError = null;
      state.createdSession = null;

      state.loadingUpdate = false;
      state.updateError = null;
      state.updatedSession = null;

      state.loadingFetchSingle = false;
      state.fetchSingleError = null;
      state.singleSession = null;
    },
  },
});

export default createUpdateEventSessionSlice.reducer;

export const {
  setLoadingCreate,
  setCreateError,
  setCreatedSession,

  setLoadingUpdate,
  setUpdateError,
  setUpdatedSession,

  setLoadingFetchSingle,
  setFetchSingleError,
  setSingleSession,

  resetCreateUpdateEventSessionState,
} = createUpdateEventSessionSlice.actions;

export const createEventSessionAction =
  (
    requestData: CreateEventSessionRequest,
    onSuccess?: (data: CreatedEventSessionData) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setCreateError(null));

    try {
      const response: CreateEventSessionResponse =
        await createEventSession(requestData);
      dispatch(setCreatedSession(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to create event session.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const updateEventSessionAction =
  (
    uuid: string,
    requestData: UpdateEventSessionRequest,
    onSuccess?: (data: UpdatedEventSessionData) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setUpdateError(null));

    try {
      const response: UpdateEventSessionResponse = await updateEventSession(
        uuid,
        requestData,
      );
      dispatch(setUpdatedSession(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update event session.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const fetchSingleEventSessionAction =
  (uuid: string, onSuccess?: (data: SingleEventSession) => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingFetchSingle(true));
    dispatch(setFetchSingleError(null));
    dispatch(setSingleSession(null));

    try {
      const response = await fetchSingleEventSession(uuid);
      dispatch(setSingleSession(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event session.";
      dispatch(setFetchSingleError(message));
    } finally {
      dispatch(setLoadingFetchSingle(false));
    }
  };
