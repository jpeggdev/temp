import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  FetchEventSessionsRequest,
  FetchEventSessionsResponse,
  SessionSummary,
} from "@/modules/eventRegistration/features/EventSessionManagement/api/fetchEventSessions/types";
import { fetchEventSessions } from "@/modules/eventRegistration/features/EventSessionManagement/api/fetchEventSessions/fetchEventSessionsApi";
import { SetPublishedEventSessionResponse } from "@/modules/eventRegistration/features/EventSessionManagement/api/setPublishedEventSession/types";
import { setPublishedEventSession } from "@/modules/eventRegistration/features/EventSessionManagement/api/setPublishedEventSession/setPublishedEventSessionApi";
import { deleteEventSession } from "@/modules/eventRegistration/features/EventSessionManagement/api/deleteEventSession/deleteEventSessionApi";

interface EventSessionListState {
  eventName: string | null;

  sessions: SessionSummary[];
  totalCount: number;

  fetchLoading: boolean;
  fetchError: string | null;

  setPublishedLoading: boolean;
  setPublishedError: string | null;

  deleteLoading: boolean;
  deleteError: string | null;
}

const initialState: EventSessionListState = {
  eventName: null,

  sessions: [],
  totalCount: 0,

  fetchLoading: false,
  fetchError: null,

  setPublishedLoading: false,
  setPublishedError: null,

  deleteLoading: false,
  deleteError: null,
};

export const eventSessionListSlice = createSlice({
  name: "eventSessionList",
  initialState,
  reducers: {
    setFetchLoading(state, action: PayloadAction<boolean>) {
      state.fetchLoading = action.payload;
    },
    setFetchError(state, action: PayloadAction<string | null>) {
      state.fetchError = action.payload;
    },
    setEventName(state, action: PayloadAction<string | null>) {
      state.eventName = action.payload;
    },
    setSessions(state, action: PayloadAction<SessionSummary[]>) {
      state.sessions = action.payload;
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
    updateSessionPublishedStatus(
      state,
      action: PayloadAction<{
        uuid: string;
        isPublished: boolean;
      }>,
    ) {
      const { uuid, isPublished } = action.payload;
      const index = state.sessions.findIndex((s) => s.uuid === uuid);
      if (index !== -1) {
        state.sessions[index].isPublished = isPublished;
      }
    },

    setDeleteLoading(state, action: PayloadAction<boolean>) {
      state.deleteLoading = action.payload;
    },
    setDeleteError(state, action: PayloadAction<string | null>) {
      state.deleteError = action.payload;
    },
    removeSession(state, action: PayloadAction<string>) {
      state.sessions = state.sessions.filter(
        (session) => session.uuid !== action.payload,
      );
      if (state.totalCount > 0) {
        state.totalCount -= 1;
      }
    },
  },
});

export default eventSessionListSlice.reducer;

export const {
  setFetchLoading,
  setFetchError,
  setEventName,
  setSessions,
  setTotalCount,

  setSetPublishedLoading,
  setSetPublishedError,
  updateSessionPublishedStatus,

  setDeleteLoading,
  setDeleteError,
  removeSession,
} = eventSessionListSlice.actions;

export const fetchEventSessionsAction =
  (params: FetchEventSessionsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setFetchLoading(true));
    dispatch(setFetchError(null));
    try {
      const result: FetchEventSessionsResponse =
        await fetchEventSessions(params);

      dispatch(setEventName(result.data.eventName));
      dispatch(setSessions(result.data.sessions));
      dispatch(setTotalCount(result.meta?.totalCount ?? 0));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event sessions.";
      dispatch(setFetchError(message));
    } finally {
      dispatch(setFetchLoading(false));
    }
  };

export const setPublishedEventSessionAction =
  (uuid: string, isPublished: boolean): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setSetPublishedLoading(true));
    dispatch(setSetPublishedError(null));
    try {
      const response: SetPublishedEventSessionResponse =
        await setPublishedEventSession({ uuid, isPublished });
      dispatch(
        updateSessionPublishedStatus({
          uuid: response.data.uuid,
          isPublished: response.data.isPublished,
        }),
      );
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to set published for event session.";
      dispatch(setSetPublishedError(message));
    } finally {
      dispatch(setSetPublishedLoading(false));
    }
  };

export const deleteEventSessionAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDeleteLoading(true));
    dispatch(setDeleteError(null));
    try {
      await deleteEventSession({
        uuid,
      });
      dispatch(removeSession(uuid));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to delete event session.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setDeleteLoading(false));
    }
  };
