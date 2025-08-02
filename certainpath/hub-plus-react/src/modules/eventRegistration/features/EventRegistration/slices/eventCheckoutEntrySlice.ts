import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  GetInProgressEventCheckoutSessionResponse,
  GetInProgressEventCheckoutSessionResponseData,
} from "@/modules/eventRegistration/features/EventRegistration/api/getInProgressEventCheckoutSession/types";
import {
  ResetEventCheckoutSessionReservationExpirationRequest,
  ResetEventCheckoutSessionReservationExpirationResponse,
  ResetEventCheckoutSessionReservationExpirationResponseData,
} from "@/modules/eventRegistration/features/EventRegistration/api/resetEventCheckoutSessionReservationExpiration/types";
import { getInProgressEventCheckoutSession } from "@/modules/eventRegistration/features/EventRegistration/api/getInProgressEventCheckoutSession/getInProgressEventCheckoutSessionApi";
import { resetEventCheckoutSessionReservationExpiration } from "@/modules/eventRegistration/features/EventRegistration/api/resetEventCheckoutSessionReservationExpiration/resetEventCheckoutSessionReservationExpirationApi";

interface EventCheckoutEntryState {
  loadingInProgress: boolean;
  inProgressError: string | null;
  inProgressData: GetInProgressEventCheckoutSessionResponseData | null;
  loadingResetReservation: boolean;
  resetReservationError: string | null;
  resetReservationData: ResetEventCheckoutSessionReservationExpirationResponseData | null;
}

const initialState: EventCheckoutEntryState = {
  loadingInProgress: false,
  inProgressError: null,
  inProgressData: null,
  loadingResetReservation: false,
  resetReservationError: null,
  resetReservationData: null,
};

const eventCheckoutEntrySlice = createSlice({
  name: "eventCheckoutEntry",
  initialState,
  reducers: {
    setLoadingInProgress(state, action: PayloadAction<boolean>) {
      state.loadingInProgress = action.payload;
    },
    setInProgressError(state, action: PayloadAction<string | null>) {
      state.inProgressError = action.payload;
    },
    setInProgressData(
      state,
      action: PayloadAction<GetInProgressEventCheckoutSessionResponseData | null>,
    ) {
      state.inProgressData = action.payload;
    },
    setLoadingResetReservation(state, action: PayloadAction<boolean>) {
      state.loadingResetReservation = action.payload;
    },
    setResetReservationError(state, action: PayloadAction<string | null>) {
      state.resetReservationError = action.payload;
    },
    setResetReservationData(
      state,
      action: PayloadAction<ResetEventCheckoutSessionReservationExpirationResponseData | null>,
    ) {
      state.resetReservationData = action.payload;
    },
    resetEventCheckoutEntryState() {
      return initialState;
    },
  },
});

export const {
  setLoadingInProgress,
  setInProgressError,
  setInProgressData,
  setLoadingResetReservation,
  setResetReservationError,
  setResetReservationData,
  resetEventCheckoutEntryState,
} = eventCheckoutEntrySlice.actions;

export const getInProgressEventCheckoutSessionAction =
  (
    eventSessionUuid: string,
    onSuccess?: (data: GetInProgressEventCheckoutSessionResponseData) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingInProgress(true));
    dispatch(setInProgressError(null));
    dispatch(setInProgressData(null));
    try {
      const response: GetInProgressEventCheckoutSessionResponse =
        await getInProgressEventCheckoutSession(eventSessionUuid);
      dispatch(setInProgressData(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const msg =
        error instanceof Error
          ? error.message
          : "Failed to fetch in-progress event checkout session.";
      dispatch(setInProgressError(msg));
    } finally {
      dispatch(setLoadingInProgress(false));
    }
  };

export const resetEventCheckoutSessionReservationExpirationAction =
  (
    requestData: ResetEventCheckoutSessionReservationExpirationRequest,
    onSuccess?: (
      data: ResetEventCheckoutSessionReservationExpirationResponseData,
    ) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingResetReservation(true));
    dispatch(setResetReservationError(null));
    try {
      const response: ResetEventCheckoutSessionReservationExpirationResponse =
        await resetEventCheckoutSessionReservationExpiration(requestData);
      dispatch(setResetReservationData(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const msg =
        error instanceof Error
          ? error.message
          : "Failed to reset event checkout session reservation expiration.";
      dispatch(setResetReservationError(msg));
    } finally {
      dispatch(setLoadingResetReservation(false));
    }
  };

export default eventCheckoutEntrySlice.reducer;
