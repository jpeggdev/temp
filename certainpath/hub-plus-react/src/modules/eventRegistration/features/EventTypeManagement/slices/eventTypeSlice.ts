import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  EventTypeItem,
  FetchEventTypesRequest,
  FetchEventTypesResponse,
} from "@/modules/eventRegistration/features/EventTypeManagement/api/fetchEventTypes/types";
import {
  FetchSingleEventTypeResponse,
  SingleEventTypeData,
} from "@/modules/eventRegistration/features/EventTypeManagement/api/fetchSingleEventType/types";
import {
  CreatedEventTypeData,
  CreateEventTypeRequest,
  CreateEventTypeResponse,
} from "@/modules/eventRegistration/features/EventTypeManagement/api/createEventType/types";
import { EditedEventTypeData } from "@/modules/eventRegistration/features/EventTypeManagement/api/editEventType/types";
import { fetchEventTypes } from "@/modules/eventRegistration/features/EventTypeManagement/api/fetchEventTypes/fetchEventTypesApi";
import { createEventType } from "@/modules/eventRegistration/features/EventTypeManagement/api/createEventType/createEventTypeApi";
import { EditEventTypeResponse } from "@/api/editEventType/types";
import { editEventType } from "@/api/editEventType/editEventTypeApi";
import { deleteEventType } from "@/modules/eventRegistration/features/EventTypeManagement/api/deleteEventType/deleteEventTypeApi";
import { fetchSingleEventType } from "@/modules/eventRegistration/features/EventTypeManagement/api/fetchSingleEventType/fetchSingleEventTypeApi";

interface EventTypeSliceState {
  eventTypes: EventTypeItem[];
  totalCount: number;
  fetchLoading: boolean;
  fetchError: string | null;
  createLoading: boolean;
  createError: string | null;
  editLoading: boolean;
  editError: string | null;
  deleteLoading: boolean;
  deleteError: string | null;
  singleLoading: boolean;
  singleError: string | null;
  singleType: SingleEventTypeData | null;
}

const initialState: EventTypeSliceState = {
  eventTypes: [],
  totalCount: 0,
  fetchLoading: false,
  fetchError: null,
  createLoading: false,
  createError: null,
  editLoading: false,
  editError: null,
  deleteLoading: false,
  deleteError: null,
  singleLoading: false,
  singleError: null,
  singleType: null,
};

const eventTypeSlice = createSlice({
  name: "eventType",
  initialState,
  reducers: {
    setFetchLoading(state, action: PayloadAction<boolean>) {
      state.fetchLoading = action.payload;
    },
    setFetchError(state, action: PayloadAction<string | null>) {
      state.fetchError = action.payload;
    },
    setEventTypes(state, action: PayloadAction<EventTypeItem[]>) {
      state.eventTypes = action.payload;
    },
    setTotalCount(state, action: PayloadAction<number>) {
      state.totalCount = action.payload;
    },
    setCreateLoading(state, action: PayloadAction<boolean>) {
      state.createLoading = action.payload;
    },
    setCreateError(state, action: PayloadAction<string | null>) {
      state.createError = action.payload;
    },
    addCreatedEventType(state, action: PayloadAction<CreatedEventTypeData>) {
      const { id, name, description, isActive } = action.payload;
      if (id != null && name != null) {
        state.eventTypes.push({
          id,
          name,
          description: description ?? null,
          isActive,
        });
        state.totalCount += 1;
      }
    },
    setEditLoading(state, action: PayloadAction<boolean>) {
      state.editLoading = action.payload;
    },
    setEditError(state, action: PayloadAction<string | null>) {
      state.editError = action.payload;
    },
    updateEventType(state, action: PayloadAction<EditedEventTypeData>) {
      const { id, name, description, isActive } = action.payload;
      const index = state.eventTypes.findIndex((t) => t.id === id);
      if (index !== -1 && name != null) {
        state.eventTypes[index].name = name;
        state.eventTypes[index].description = description ?? null;
        state.eventTypes[index].isActive = isActive;
      }
    },
    setDeleteLoading(state, action: PayloadAction<boolean>) {
      state.deleteLoading = action.payload;
    },
    setDeleteError(state, action: PayloadAction<string | null>) {
      state.deleteError = action.payload;
    },
    removeEventType(state, action: PayloadAction<number>) {
      state.eventTypes = state.eventTypes.filter(
        (type) => type.id !== action.payload,
      );
      if (state.totalCount > 0) {
        state.totalCount -= 1;
      }
    },
    setSingleLoading(state, action: PayloadAction<boolean>) {
      state.singleLoading = action.payload;
    },
    setSingleError(state, action: PayloadAction<string | null>) {
      state.singleError = action.payload;
    },
    setSingleType(state, action: PayloadAction<SingleEventTypeData | null>) {
      state.singleType = action.payload;
    },
  },
});

export default eventTypeSlice.reducer;

export const {
  setFetchLoading,
  setFetchError,
  setEventTypes,
  setTotalCount,
  setCreateLoading,
  setCreateError,
  addCreatedEventType,
  setEditLoading,
  setEditError,
  updateEventType,
  setDeleteLoading,
  setDeleteError,
  removeEventType,
  setSingleLoading,
  setSingleError,
  setSingleType,
} = eventTypeSlice.actions;

export const fetchEventTypesAction =
  (params: FetchEventTypesRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setFetchLoading(true));
    dispatch(setFetchError(null));
    try {
      const response: FetchEventTypesResponse = await fetchEventTypes(params);
      dispatch(setEventTypes(response.data.eventTypes));
      dispatch(setTotalCount(response.data.totalCount));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to fetch event types.";
      dispatch(setFetchError(message));
    } finally {
      dispatch(setFetchLoading(false));
    }
  };

export const createEventTypeAction =
  (requestBody: CreateEventTypeRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setCreateLoading(true));
    dispatch(setCreateError(null));
    try {
      const response: CreateEventTypeResponse =
        await createEventType(requestBody);
      dispatch(addCreatedEventType(response.data));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to create event type.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setCreateLoading(false));
    }
  };

export const editEventTypeAction =
  (
    id: number,
    name: string,
    description: string | null,
    isActive: boolean,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setEditLoading(true));
    dispatch(setEditError(null));
    try {
      const response: EditEventTypeResponse = await editEventType(id, {
        name,
        description,
        isActive,
      });
      dispatch(updateEventType(response.data));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to edit event type.";
      dispatch(setEditError(message));
    } finally {
      dispatch(setEditLoading(false));
    }
  };

export const deleteEventTypeAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDeleteLoading(true));
    dispatch(setDeleteError(null));
    try {
      await deleteEventType({ id });
      dispatch(removeEventType(id));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to delete event type.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setDeleteLoading(false));
    }
  };

export const fetchSingleEventTypeAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setSingleLoading(true));
    dispatch(setSingleError(null));
    try {
      const response: FetchSingleEventTypeResponse = await fetchSingleEventType(
        {
          id,
        },
      );
      dispatch(setSingleType(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event type details.";
      dispatch(setSingleError(message));
    } finally {
      dispatch(setSingleLoading(false));
    }
  };
