import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  FetchEventsRequest,
  FetchEventsResponse,
} from "@/modules/eventRegistration/features/EventManagement/api/fetchEvents/types";
import { fetchEvents } from "@/modules/eventRegistration/features/EventManagement/api/fetchEvents/fetchEventsApi";
import {
  SetPublishedEventRequest,
  SetPublishedEventResponse,
} from "@/modules/eventRegistration/features/EventManagement/api/setPublishedEvent/types";
import { setPublishedEvent } from "@/modules/eventRegistration/features/EventManagement/api/setPublishedEvent/setPublishedEventApi";
import { duplicateEvent } from "@/modules/eventRegistration/features/EventManagement/api/duplicateEvent/duplicateEventApi";
import { DeleteEventResponse } from "@/modules/eventRegistration/features/EventManagement/api/deleteEvent/types";
import { deleteEvent } from "@/modules/eventRegistration/features/EventManagement/api/deleteEvent/deleteEventApi";
import { FetchEventFilterMetadataResponse } from "@/modules/eventRegistration/features/EventManagement/api/fetchEventFilterMetadata/types";
import { fetchEventFilterMetadata } from "@/modules/eventRegistration/features/EventManagement/api/fetchEventFilterMetadata/fetchEventFilterMetadataApi";

export interface EventItem {
  id: number;
  uuid: string;
  eventCode: string;
  eventName: string;
  eventDescription: string;
  isPublished: boolean;
  eventPrice: number;
  thumbnailUrl: string | null;
  eventTypeName: string | null;
  eventCategoryName: string | null;
  createdAt: string | null;
}

export interface EventFilterMetadataItem {
  id: number;
  name: string;
}

export interface EventFilterMetadata {
  eventTypes: EventFilterMetadataItem[];
  eventCategories: EventFilterMetadataItem[];
  employeeRoles: EventFilterMetadataItem[];
  trades: EventFilterMetadataItem[];
  eventTags: EventFilterMetadataItem[];
}

interface EventListState {
  events: EventItem[];
  totalCount: number;

  fetchLoading: boolean;
  fetchError: string | null;

  setPublishedLoading: boolean;
  setPublishedError: string | null;

  duplicateLoading: boolean;
  duplicateError: string | null;

  deleteLoading: boolean;
  deleteError: string | null;

  filterMetadataLoading: boolean;
  filterMetadataError: string | null;
  eventTypes: EventFilterMetadataItem[];
  eventCategories: EventFilterMetadataItem[];
  employeeRoles: EventFilterMetadataItem[];
  trades: EventFilterMetadataItem[];
  eventTags: EventFilterMetadataItem[];
}

const initialState: EventListState = {
  events: [],
  totalCount: 0,

  fetchLoading: false,
  fetchError: null,

  setPublishedLoading: false,
  setPublishedError: null,

  duplicateLoading: false,
  duplicateError: null,

  deleteLoading: false,
  deleteError: null,

  filterMetadataLoading: false,
  filterMetadataError: null,
  eventTypes: [],
  eventCategories: [],
  employeeRoles: [],
  trades: [],
  eventTags: [],
};

export const eventListSlice = createSlice({
  name: "eventList",
  initialState,
  reducers: {
    setFetchLoading(state, action: PayloadAction<boolean>) {
      state.fetchLoading = action.payload;
    },
    setFetchError(state, action: PayloadAction<string | null>) {
      state.fetchError = action.payload;
    },
    setEvents(state, action: PayloadAction<EventItem[]>) {
      state.events = action.payload;
    },
    setTotalCount(state, action: PayloadAction<number>) {
      state.totalCount = action.payload;
    },
    setSetPublishedLoading(state, action: PayloadAction<boolean>) {
      state.setPublishedLoading = action.payload;
    },
    setSetPublishedError(state, action: PayloadAction<string | null>) {
      state.setPublishedError = action.payload;
    },
    updateEventPublishedStatus(
      state,
      action: PayloadAction<{
        uuid: string;
        isPublished: boolean;
        eventName?: string;
      }>,
    ) {
      const { uuid, isPublished, eventName } = action.payload;
      const index = state.events.findIndex((e) => e.uuid === uuid);
      if (index !== -1) {
        state.events[index].isPublished = isPublished;
        if (eventName !== undefined) {
          state.events[index].eventName = eventName;
        }
      }
    },
    setDuplicateLoading(state, action: PayloadAction<boolean>) {
      state.duplicateLoading = action.payload;
    },
    setDuplicateError(state, action: PayloadAction<string | null>) {
      state.duplicateError = action.payload;
    },
    setDeleteLoading(state, action: PayloadAction<boolean>) {
      state.deleteLoading = action.payload;
    },
    setDeleteError(state, action: PayloadAction<string | null>) {
      state.deleteError = action.payload;
    },
    removeEvent(state, action: PayloadAction<number>) {
      const idToRemove = action.payload;
      state.events = state.events.filter((e) => e.id !== idToRemove);
      state.totalCount -= 1;
    },
    setFilterMetadataLoading(state, action: PayloadAction<boolean>) {
      state.filterMetadataLoading = action.payload;
    },
    setFilterMetadataError(state, action: PayloadAction<string | null>) {
      state.filterMetadataError = action.payload;
    },
    setEventTypes(state, action: PayloadAction<EventFilterMetadataItem[]>) {
      state.eventTypes = action.payload;
    },
    setEventCategories(
      state,
      action: PayloadAction<EventFilterMetadataItem[]>,
    ) {
      state.eventCategories = action.payload;
    },
    setEmployeeRoles(state, action: PayloadAction<EventFilterMetadataItem[]>) {
      state.employeeRoles = action.payload;
    },
    setTrades(state, action: PayloadAction<EventFilterMetadataItem[]>) {
      state.trades = action.payload;
    },
    setEventTags(state, action: PayloadAction<EventFilterMetadataItem[]>) {
      state.eventTags = action.payload;
    },
  },
});

export default eventListSlice.reducer;

export const {
  setFetchLoading,
  setFetchError,
  setEvents,
  setTotalCount,

  setSetPublishedLoading,
  setSetPublishedError,
  updateEventPublishedStatus,

  setDuplicateLoading,
  setDuplicateError,

  setDeleteLoading,
  setDeleteError,
  removeEvent,

  setFilterMetadataLoading,
  setFilterMetadataError,
  setEventTypes,
  setEventCategories,
  setEmployeeRoles,
  setTrades,
  setEventTags,
} = eventListSlice.actions;

export const fetchEventsAction =
  (params: FetchEventsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setFetchLoading(true));
    dispatch(setFetchError(null));
    try {
      const result: FetchEventsResponse = await fetchEvents(params);
      dispatch(setEvents(result.data));
      dispatch(setTotalCount(result.meta?.totalCount ?? 0));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to fetch events";
      dispatch(setFetchError(message));
    } finally {
      dispatch(setFetchLoading(false));
    }
  };

export const setPublishedEventAction =
  (uuid: string, isPublished: boolean): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setSetPublishedLoading(true));
    dispatch(setSetPublishedError(null));
    try {
      const response: SetPublishedEventResponse = await setPublishedEvent(
        uuid,
        {
          isPublished,
        } as SetPublishedEventRequest,
      );
      dispatch(
        updateEventPublishedStatus({
          uuid: response.data.uuid,
          isPublished: response.data.isPublished,
          eventName: response.data.eventName,
        }),
      );
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to set published event.";
      dispatch(setSetPublishedError(message));
    } finally {
      dispatch(setSetPublishedLoading(false));
    }
  };

export const duplicateEventAction =
  (eventId: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDuplicateLoading(true));
    dispatch(setDuplicateError(null));
    try {
      await duplicateEvent(eventId);
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to duplicate event.";
      dispatch(setDuplicateError(message));
    } finally {
      dispatch(setDuplicateLoading(false));
    }
  };

export const deleteEventAction =
  (eventId: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDeleteLoading(true));
    dispatch(setDeleteError(null));
    try {
      const response: DeleteEventResponse = await deleteEvent(eventId);
      dispatch(removeEvent(response.data.id));

      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to delete event.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setDeleteLoading(false));
    }
  };

export const fetchEventFilterMetadataAction =
  (): AppThunk => async (dispatch) => {
    dispatch(setFilterMetadataLoading(true));
    dispatch(setFilterMetadataError(null));
    try {
      const response: FetchEventFilterMetadataResponse =
        await fetchEventFilterMetadata();
      const metadata: EventFilterMetadata = response.data;
      dispatch(
        setEventTypes(
          metadata.eventTypes.map((t) => ({ id: t.id, name: t.name })),
        ),
      );
      dispatch(
        setEventCategories(
          metadata.eventCategories.map((c) => ({ id: c.id, name: c.name })),
        ),
      );
      dispatch(
        setEmployeeRoles(
          metadata.employeeRoles.map((r) => ({ id: r.id, name: r.name })),
        ),
      );
      dispatch(
        setTrades(metadata.trades.map((tr) => ({ id: tr.id, name: tr.name }))),
      );
      dispatch(
        setEventTags(
          metadata.eventTags.map((tg) => ({ id: tg.id, name: tg.name })),
        ),
      );
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event filter metadata.";
      dispatch(setFilterMetadataError(message));
    } finally {
      dispatch(setFilterMetadataLoading(false));
    }
  };
