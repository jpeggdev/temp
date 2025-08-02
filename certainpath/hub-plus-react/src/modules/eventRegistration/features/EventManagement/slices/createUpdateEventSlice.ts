import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateEventRequest,
  CreateEventResponse,
  CreateEventResponseData,
} from "@/modules/eventRegistration/features/EventManagement/api/createEvent/types";
import {
  FetchEventRequest,
  FetchEventResponse,
  SingleEventData,
} from "@/modules/eventRegistration/features/EventManagement/api/fetchEvent/types";
import {
  UpdateEventRequest,
  UpdateEventResponse,
  UpdateEventResponseData,
} from "@/modules/eventRegistration/features/EventManagement/api/updateEvent/types";
import { createEvent } from "@/modules/eventRegistration/features/EventManagement/api/createEvent/createEventApi";
import { fetchEvent } from "@/modules/eventRegistration/features/EventManagement/api/fetchEvent/fetchEventApi";
import { updateEvent } from "@/modules/eventRegistration/features/EventManagement/api/updateEvent/updateEventApi";
import { GetCreateUpdateEventMetadataResponse } from "@/modules/eventRegistration/features/EventManagement/api/getCreateUpdateEventMetadata/types";
import { fetchCreateUpdateEventMetadata } from "@/modules/eventRegistration/features/EventManagement/api/getCreateUpdateEventMetadata/getCreateUpdateEventMetadataApi";

interface CreateUpdateEventState {
  loadingCreate: boolean;
  createError: string | null;
  createdEvent: CreateEventResponseData | null;
  loadingGet: boolean;
  getError: string | null;
  fetchedEvent: SingleEventData | null;
  loadingUpdate: boolean;
  updateError: string | null;
  updatedEvent: UpdateEventResponseData | null;
  loadingMetadata: boolean;
  metadataError: string | null;
  createUpdateEventMetadata:
    | GetCreateUpdateEventMetadataResponse["data"]
    | null;
}

const initialState: CreateUpdateEventState = {
  loadingCreate: false,
  createError: null,
  createdEvent: null,
  loadingGet: false,
  getError: null,
  fetchedEvent: null,
  loadingUpdate: false,
  updateError: null,
  updatedEvent: null,
  loadingMetadata: false,
  metadataError: null,
  createUpdateEventMetadata: null,
};

const createUpdateEventSlice = createSlice({
  name: "createUpdateEvent",
  initialState,
  reducers: {
    setLoadingCreate(state, action: PayloadAction<boolean>) {
      state.loadingCreate = action.payload;
    },
    setCreateError(state, action: PayloadAction<string | null>) {
      state.createError = action.payload;
    },
    setCreatedEvent(
      state,
      action: PayloadAction<CreateEventResponseData | null>,
    ) {
      state.createdEvent = action.payload;
    },
    setLoadingGet(state, action: PayloadAction<boolean>) {
      state.loadingGet = action.payload;
    },
    setGetError(state, action: PayloadAction<string | null>) {
      state.getError = action.payload;
    },
    setFetchedEvent(state, action: PayloadAction<SingleEventData | null>) {
      state.fetchedEvent = action.payload;
    },
    setLoadingUpdate(state, action: PayloadAction<boolean>) {
      state.loadingUpdate = action.payload;
    },
    setUpdateError(state, action: PayloadAction<string | null>) {
      state.updateError = action.payload;
    },
    setUpdatedEvent(
      state,
      action: PayloadAction<UpdateEventResponseData | null>,
    ) {
      state.updatedEvent = action.payload;
    },
    setLoadingMetadata(state, action: PayloadAction<boolean>) {
      state.loadingMetadata = action.payload;
    },
    setMetadataError(state, action: PayloadAction<string | null>) {
      state.metadataError = action.payload;
    },
    setCreateUpdateEventMetadata(
      state,
      action: PayloadAction<
        GetCreateUpdateEventMetadataResponse["data"] | null
      >,
    ) {
      state.createUpdateEventMetadata = action.payload;
    },
    resetCreateUpdateEventState(state) {
      state.loadingCreate = false;
      state.createError = null;
      state.createdEvent = null;

      state.loadingGet = false;
      state.getError = null;
      state.fetchedEvent = null;

      state.loadingUpdate = false;
      state.updateError = null;
      state.updatedEvent = null;

      state.loadingMetadata = false;
      state.metadataError = null;
      state.createUpdateEventMetadata = null;
    },
  },
});

export const {
  setLoadingCreate,
  setCreateError,
  setCreatedEvent,

  setLoadingGet,
  setGetError,
  setFetchedEvent,

  setLoadingUpdate,
  setUpdateError,
  setUpdatedEvent,

  setLoadingMetadata,
  setMetadataError,
  setCreateUpdateEventMetadata,

  resetCreateUpdateEventState,
} = createUpdateEventSlice.actions;

export default createUpdateEventSlice.reducer;

export const createEventAction =
  (
    requestData: CreateEventRequest,
    onSuccess?: (data: CreateEventResponseData) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setCreateError(null));
    try {
      const response: CreateEventResponse = await createEvent(requestData);
      dispatch(setCreatedEvent(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to create the event.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const getEventAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingGet(true));
    dispatch(setGetError(null));
    try {
      const requestData: FetchEventRequest = { uuid };
      const response: FetchEventResponse = await fetchEvent(requestData);
      dispatch(setFetchedEvent(response.data));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to fetch the event.";
      dispatch(setGetError(message));
    } finally {
      dispatch(setLoadingGet(false));
    }
  };

export const updateEventAction =
  (
    eventId: number,
    requestData: UpdateEventRequest,
    onSuccess?: (data: UpdateEventResponseData) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setUpdateError(null));
    try {
      const response: UpdateEventResponse = await updateEvent(
        eventId,
        requestData,
      );
      dispatch(setUpdatedEvent(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to update the event.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const fetchCreateUpdateEventMetadataAction = (): AppThunk => {
  return async (dispatch: AppDispatch) => {
    dispatch(setLoadingMetadata(true));
    dispatch(setMetadataError(null));
    try {
      const responseData = await fetchCreateUpdateEventMetadata();
      dispatch(setCreateUpdateEventMetadata(responseData.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch create/update event metadata.";
      dispatch(setMetadataError(message));
    } finally {
      dispatch(setLoadingMetadata(false));
    }
  };
};
