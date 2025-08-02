import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  GetEventCheckoutSessionDetailsResponse,
  GetEventCheckoutSessionDetailsResponseData,
} from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutSessionDetails/types";
import {
  CreateEventCheckoutSessionRequest,
  CreateEventCheckoutSessionResponse,
  CreateEventCheckoutSessionResponseData,
} from "@/modules/eventRegistration/features/EventRegistration/api/createEventCheckoutSession/types";
import { createEventCheckoutSession } from "@/modules/eventRegistration/features/EventRegistration/api/createEventCheckoutSession/createEventCheckoutSessionApi";
import {
  UpdateEventCheckoutSessionRequest,
  UpdateEventCheckoutSessionResponse,
  UpdateEventCheckoutSessionResponseData,
} from "@/modules/eventRegistration/features/EventRegistration/api/updateEventCheckoutSession/types";
import { updateEventCheckoutSession } from "@/modules/eventRegistration/features/EventRegistration/api/updateEventCheckoutSession/updateEventCheckoutSessionApi";
import { getEventCheckoutSessionDetails } from "@/modules/eventRegistration/features/EventRegistration/api/getEventCheckoutSessionDetails/getEventCheckoutSessionDetailsApi";
import {
  UpdateEventCheckoutAttendeeWaitlistRequest,
  UpdateEventCheckoutAttendeeWaitlistResponse,
  UpdateEventCheckoutAttendeeWaitlistResponseData,
} from "@/modules/eventRegistration/features/EventRegistration/api/updateEventCheckoutAttendeeWaitlist/types";
import { updateEventCheckoutAttendeeWaitlist } from "@/modules/eventRegistration/features/EventRegistration/api/updateEventCheckoutAttendeeWaitlist/updateEventCheckoutAttendeeWaitlistApi";

interface EventCheckoutState {
  loadingCreate: boolean;
  createError: string | null;
  createdSession: CreateEventCheckoutSessionResponseData | null;
  loadingUpdate: boolean;
  updateError: string | null;
  updatedSession: UpdateEventCheckoutSessionResponseData | null;
  loadingGetDetails: boolean;
  getDetailsError: string | null;
  eventCheckoutSessionDetails: GetEventCheckoutSessionDetailsResponseData | null;
  loadingUpdateWaitlist: boolean;
  updateWaitlistError: string | null;
  updatedWaitlistAttendees: UpdateEventCheckoutAttendeeWaitlistResponseData | null;
}

const initialState: EventCheckoutState = {
  loadingCreate: false,
  createError: null,
  createdSession: null,
  loadingUpdate: false,
  updateError: null,
  updatedSession: null,
  loadingGetDetails: false,
  getDetailsError: null,
  eventCheckoutSessionDetails: null,
  loadingUpdateWaitlist: false,
  updateWaitlistError: null,
  updatedWaitlistAttendees: null,
};

const eventCheckoutSlice = createSlice({
  name: "eventCheckout",
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
      action: PayloadAction<EventCheckoutState["createdSession"]>,
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
      action: PayloadAction<EventCheckoutState["updatedSession"]>,
    ) {
      state.updatedSession = action.payload;
    },
    setLoadingGetDetails(state, action: PayloadAction<boolean>) {
      state.loadingGetDetails = action.payload;
    },
    setGetDetailsError(state, action: PayloadAction<string | null>) {
      state.getDetailsError = action.payload;
    },
    setEventCheckoutSessionDetails(
      state,
      action: PayloadAction<GetEventCheckoutSessionDetailsResponseData | null>,
    ) {
      state.eventCheckoutSessionDetails = action.payload;
    },
    setLoadingUpdateWaitlist(state, action: PayloadAction<boolean>) {
      state.loadingUpdateWaitlist = action.payload;
    },
    setUpdateWaitlistError(state, action: PayloadAction<string | null>) {
      state.updateWaitlistError = action.payload;
    },
    setUpdatedWaitlistAttendees(
      state,
      action: PayloadAction<EventCheckoutState["updatedWaitlistAttendees"]>,
    ) {
      state.updatedWaitlistAttendees = action.payload;
    },
    resetEventCheckoutState() {
      return initialState;
    },
  },
});

export const {
  setLoadingCreate,
  setCreateError,
  setCreatedSession,
  setLoadingUpdate,
  setUpdateError,
  setUpdatedSession,
  setLoadingGetDetails,
  setGetDetailsError,
  setEventCheckoutSessionDetails,
  setLoadingUpdateWaitlist,
  setUpdateWaitlistError,
  setUpdatedWaitlistAttendees,
  resetEventCheckoutState,
} = eventCheckoutSlice.actions;

export const createEventCheckoutSessionAction =
  (
    requestData: CreateEventCheckoutSessionRequest,
    onSuccess?: (data: CreateEventCheckoutSessionResponseData) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setCreateError(null));
    try {
      const response: CreateEventCheckoutSessionResponse =
        await createEventCheckoutSession(requestData);
      dispatch(setCreatedSession(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to create an event checkout session.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const updateEventCheckoutSessionAction =
  (
    eventCheckoutSessionUuid: string,
    requestData: UpdateEventCheckoutSessionRequest,
    onSuccess?: (data: EventCheckoutState["updatedSession"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setUpdateError(null));
    try {
      const response: UpdateEventCheckoutSessionResponse =
        await updateEventCheckoutSession(eventCheckoutSessionUuid, requestData);
      dispatch(setUpdatedSession(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update this event checkout session.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const getEventCheckoutSessionDetailsAction =
  (
    eventCheckoutSessionUuid: string,
    onSuccess?: (data: GetEventCheckoutSessionDetailsResponseData) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingGetDetails(true));
    dispatch(setGetDetailsError(null));
    dispatch(setEventCheckoutSessionDetails(null));
    try {
      const response: GetEventCheckoutSessionDetailsResponse =
        await getEventCheckoutSessionDetails(eventCheckoutSessionUuid);
      dispatch(setEventCheckoutSessionDetails(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event checkout session details.";
      dispatch(setGetDetailsError(message));
    } finally {
      dispatch(setLoadingGetDetails(false));
    }
  };

export const updateEventCheckoutAttendeeWaitlistAction =
  (
    eventCheckoutSessionUuid: string,
    requestData: UpdateEventCheckoutAttendeeWaitlistRequest,
    onSuccess?: (data: UpdateEventCheckoutAttendeeWaitlistResponseData) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdateWaitlist(true));
    dispatch(setUpdateWaitlistError(null));
    try {
      const response: UpdateEventCheckoutAttendeeWaitlistResponse =
        await updateEventCheckoutAttendeeWaitlist(
          eventCheckoutSessionUuid,
          requestData,
        );
      dispatch(setUpdatedWaitlistAttendees(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update attendee waitlist status.";
      dispatch(setUpdateWaitlistError(message));
    } finally {
      dispatch(setLoadingUpdateWaitlist(false));
    }
  };

export default eventCheckoutSlice.reducer;
