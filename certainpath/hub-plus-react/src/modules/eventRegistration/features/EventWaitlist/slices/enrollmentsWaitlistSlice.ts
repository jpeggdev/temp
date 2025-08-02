import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  EventEnrollmentItemResponseDTO,
  FetchEventEnrollmentsRequest,
} from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventEnrollments/types";
import {
  EventWaitlistItemResponse,
  FetchEventWaitlistItemsRequest,
} from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventWaitlistItems/types";
import {
  FetchWaitlistDetailsRequest,
  WaitlistDetails,
} from "@/modules/eventRegistration/features/EventWaitlist/api/fetchWaitlistDetails/types";
import { fetchEventEnrollments } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventEnrollments/fetchEventEnrollmentsApi";
import { fetchEventWaitlistItems } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchEventWaitlistItems/fetchEventWaitlistItemsApi";
import { fetchWaitlistDetails } from "@/modules/eventRegistration/features/EventWaitlist/api/fetchWaitlistDetails/fetchWaitlistDetailsApi";
import {
  MoveWaitlistToEnrollmentRequest,
  MoveWaitlistToEnrollmentResponse,
  MoveWaitlistToEnrollmentResult,
} from "@/modules/eventRegistration/features/EventWaitlist/api/moveWaitlistToEnrollment/types";
import {
  RemoveWaitlistItemRequest,
  RemoveWaitlistItemResponse,
  RemoveWaitlistItemResult,
} from "@/modules/eventRegistration/features/EventWaitlist/api/removeWaitlistItem/types";
import {
  ReplaceEnrollmentAttendeeRequest,
  ReplaceEnrollmentAttendeeResponse,
  ReplaceEnrollmentAttendeeResult,
} from "@/modules/eventRegistration/features/EventWaitlist/api/replaceEnrollmentAttendee/types";
import {
  ReplaceEnrollmentWithEmployeeRequest,
  ReplaceEnrollmentWithEmployeeResponse,
  ReplaceEnrollmentWithEmployeeResult,
} from "@/modules/eventRegistration/features/EventWaitlist/api/replaceEnrollmentWithEmployee/types";
import {
  UpdateWaitlistPositionRequest,
  UpdateWaitlistPositionResponse,
  UpdateWaitlistPositionResult,
} from "@/modules/eventRegistration/features/EventWaitlist/api/updateWaitlistPosition/types";
import { removeWaitlistItem } from "@/modules/eventRegistration/features/EventWaitlist/api/removeWaitlistItem/removeWaitlistItemApi";
import { replaceEnrollmentAttendee } from "@/modules/eventRegistration/features/EventWaitlist/api/replaceEnrollmentAttendee/replaceEnrollmentAttendeeApi";
import { replaceEnrollmentWithEmployee } from "@/modules/eventRegistration/features/EventWaitlist/api/replaceEnrollmentWithEmployee/replaceEnrollmentWithEmployeeApi";
import { updateWaitlistPosition } from "@/modules/eventRegistration/features/EventWaitlist/api/updateWaitlistPosition/updateWaitlistPositionApi";
import {
  MoveEnrollmentToWaitlistRequest,
  MoveEnrollmentToWaitlistResponse,
  MoveEnrollmentToWaitlistResult,
} from "@/modules/eventRegistration/features/EventWaitlist/api/moveEnrollmentToWaitlist/types";
import { moveEnrollmentToWaitlist } from "@/modules/eventRegistration/features/EventWaitlist/api/moveEnrollmentToWaitlist/moveEnrollmentToWaitlistApi";
import { moveWaitlistToEnrollment } from "@/modules/eventRegistration/features/EventWaitlist/api/moveWaitlistToEnrollment/moveWaitlistToEnrollmentApi";

interface EnrollmentsWaitlistSliceState {
  loadingEnrollments: boolean;
  enrollmentsError: string | null;
  enrollments: EventEnrollmentItemResponseDTO[];
  totalEnrollments: number;
  loadingWaitlistItems: boolean;
  waitlistItemsError: string | null;
  waitlistItems: EventWaitlistItemResponse[];
  totalWaitlistItems: number;
  loadingWaitlistDetails: boolean;
  waitlistDetailsError: string | null;
  waitlistDetails: WaitlistDetails | null;

  loadingMoveWaitlist: boolean;
  errorMoveWaitlist: string | null;
  moveWaitlistResult: MoveWaitlistToEnrollmentResult | null;

  loadingRemoveWaitlist: boolean;
  errorRemoveWaitlist: string | null;
  removeWaitlistResult: RemoveWaitlistItemResult | null;

  loadingReplaceAttendee: boolean;
  errorReplaceAttendee: string | null;
  replaceAttendeeResult: ReplaceEnrollmentAttendeeResult | null;

  loadingReplaceWithEmployee: boolean;
  errorReplaceWithEmployee: string | null;
  replaceWithEmployeeResult: ReplaceEnrollmentWithEmployeeResult | null;

  loadingUpdatePosition: boolean;
  errorUpdatePosition: string | null;
  updatePositionResult: UpdateWaitlistPositionResult | null;

  loadingMoveEnrollment: boolean;
  errorMoveEnrollment: string | null;
  moveEnrollmentResult: MoveEnrollmentToWaitlistResult | null;
}

const initialState: EnrollmentsWaitlistSliceState = {
  loadingEnrollments: false,
  enrollmentsError: null,
  enrollments: [],
  totalEnrollments: 0,

  loadingWaitlistItems: false,
  waitlistItemsError: null,
  waitlistItems: [],
  totalWaitlistItems: 0,

  loadingWaitlistDetails: false,
  waitlistDetailsError: null,
  waitlistDetails: null,

  loadingMoveWaitlist: false,
  errorMoveWaitlist: null,
  moveWaitlistResult: null,

  loadingRemoveWaitlist: false,
  errorRemoveWaitlist: null,
  removeWaitlistResult: null,

  loadingReplaceAttendee: false,
  errorReplaceAttendee: null,
  replaceAttendeeResult: null,

  loadingReplaceWithEmployee: false,
  errorReplaceWithEmployee: null,
  replaceWithEmployeeResult: null,

  loadingUpdatePosition: false,
  errorUpdatePosition: null,
  updatePositionResult: null,

  loadingMoveEnrollment: false,
  errorMoveEnrollment: null,
  moveEnrollmentResult: null,
};

const enrollmentsWaitlistSlice = createSlice({
  name: "enrollmentsWaitlist",
  initialState,
  reducers: {
    setLoadingEnrollments(state, action: PayloadAction<boolean>) {
      state.loadingEnrollments = action.payload;
    },
    setEnrollmentsError(state, action: PayloadAction<string | null>) {
      state.enrollmentsError = action.payload;
    },
    setEnrollments(
      state,
      action: PayloadAction<EventEnrollmentItemResponseDTO[]>,
    ) {
      state.enrollments = action.payload;
    },
    setTotalEnrollments(state, action: PayloadAction<number>) {
      state.totalEnrollments = action.payload;
    },
    setLoadingWaitlistItems(state, action: PayloadAction<boolean>) {
      state.loadingWaitlistItems = action.payload;
    },
    setWaitlistItemsError(state, action: PayloadAction<string | null>) {
      state.waitlistItemsError = action.payload;
    },
    setWaitlistItems(
      state,
      action: PayloadAction<EventWaitlistItemResponse[]>,
    ) {
      state.waitlistItems = action.payload;
    },
    setTotalWaitlistItems(state, action: PayloadAction<number>) {
      state.totalWaitlistItems = action.payload;
    },
    setLoadingWaitlistDetails(state, action: PayloadAction<boolean>) {
      state.loadingWaitlistDetails = action.payload;
    },
    setWaitlistDetailsError(state, action: PayloadAction<string | null>) {
      state.waitlistDetailsError = action.payload;
    },
    setWaitlistDetails(state, action: PayloadAction<WaitlistDetails | null>) {
      state.waitlistDetails = action.payload;
    },

    setLoadingMoveWaitlist(state, action: PayloadAction<boolean>) {
      state.loadingMoveWaitlist = action.payload;
    },
    setErrorMoveWaitlist(state, action: PayloadAction<string | null>) {
      state.errorMoveWaitlist = action.payload;
    },
    setMoveWaitlistResult(
      state,
      action: PayloadAction<MoveWaitlistToEnrollmentResult | null>,
    ) {
      state.moveWaitlistResult = action.payload;
    },

    setLoadingRemoveWaitlist(state, action: PayloadAction<boolean>) {
      state.loadingRemoveWaitlist = action.payload;
    },
    setErrorRemoveWaitlist(state, action: PayloadAction<string | null>) {
      state.errorRemoveWaitlist = action.payload;
    },
    setRemoveWaitlistResult(
      state,
      action: PayloadAction<RemoveWaitlistItemResult | null>,
    ) {
      state.removeWaitlistResult = action.payload;
    },

    setLoadingReplaceAttendee(state, action: PayloadAction<boolean>) {
      state.loadingReplaceAttendee = action.payload;
    },
    setErrorReplaceAttendee(state, action: PayloadAction<string | null>) {
      state.errorReplaceAttendee = action.payload;
    },
    setReplaceAttendeeResult(
      state,
      action: PayloadAction<ReplaceEnrollmentAttendeeResult | null>,
    ) {
      state.replaceAttendeeResult = action.payload;
    },

    setLoadingReplaceWithEmployee(state, action: PayloadAction<boolean>) {
      state.loadingReplaceWithEmployee = action.payload;
    },
    setErrorReplaceWithEmployee(state, action: PayloadAction<string | null>) {
      state.errorReplaceWithEmployee = action.payload;
    },
    setReplaceWithEmployeeResult(
      state,
      action: PayloadAction<ReplaceEnrollmentWithEmployeeResult | null>,
    ) {
      state.replaceWithEmployeeResult = action.payload;
    },

    setLoadingUpdatePosition(state, action: PayloadAction<boolean>) {
      state.loadingUpdatePosition = action.payload;
    },
    setErrorUpdatePosition(state, action: PayloadAction<string | null>) {
      state.errorUpdatePosition = action.payload;
    },
    setUpdatePositionResult(
      state,
      action: PayloadAction<UpdateWaitlistPositionResult | null>,
    ) {
      state.updatePositionResult = action.payload;
    },

    updateWaitlistItemPositionOptimistically(
      state,
      action: PayloadAction<{ waitlistId: number; newPosition: number }>,
    ) {
      const { waitlistId, newPosition } = action.payload;
      const itemIndex = state.waitlistItems.findIndex(
        (item) => item.id === waitlistId,
      );
      if (itemIndex === -1) return;
      let newWaitlistItems = [...state.waitlistItems];

      const [movedItem] = newWaitlistItems.splice(itemIndex, 1);

      movedItem.waitlistPosition = newPosition;
      const targetIndex = newPosition - 1;

      newWaitlistItems.splice(
        Math.min(targetIndex, newWaitlistItems.length),
        0,
        movedItem,
      );

      newWaitlistItems = newWaitlistItems.map((item, index) => ({
        ...item,
        waitlistPosition: index + 1,
      }));

      state.waitlistItems = newWaitlistItems;
    },

    setLoadingMoveEnrollment(state, action: PayloadAction<boolean>) {
      state.loadingMoveEnrollment = action.payload;
    },
    setErrorMoveEnrollment(state, action: PayloadAction<string | null>) {
      state.errorMoveEnrollment = action.payload;
    },
    setMoveEnrollmentResult(
      state,
      action: PayloadAction<MoveEnrollmentToWaitlistResult | null>,
    ) {
      state.moveEnrollmentResult = action.payload;
    },

    clearEnrollmentsWaitlistState() {
      return initialState;
    },
  },
});

export default enrollmentsWaitlistSlice.reducer;

export const {
  setLoadingEnrollments,
  setEnrollmentsError,
  setEnrollments,
  setTotalEnrollments,
  setLoadingWaitlistItems,
  setWaitlistItemsError,
  setWaitlistItems,
  setTotalWaitlistItems,
  setLoadingWaitlistDetails,
  setWaitlistDetailsError,
  setWaitlistDetails,
  setLoadingMoveWaitlist,
  setErrorMoveWaitlist,
  setMoveWaitlistResult,
  setLoadingRemoveWaitlist,
  setErrorRemoveWaitlist,
  setRemoveWaitlistResult,
  setLoadingReplaceAttendee,
  setErrorReplaceAttendee,
  setReplaceAttendeeResult,
  setLoadingReplaceWithEmployee,
  setErrorReplaceWithEmployee,
  setReplaceWithEmployeeResult,
  setLoadingUpdatePosition,
  setErrorUpdatePosition,
  setUpdatePositionResult,
  updateWaitlistItemPositionOptimistically,

  setLoadingMoveEnrollment,
  setErrorMoveEnrollment,
  setMoveEnrollmentResult,

  clearEnrollmentsWaitlistState,
} = enrollmentsWaitlistSlice.actions;

export const fetchEventEnrollmentsAction =
  (requestData: FetchEventEnrollmentsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoadingEnrollments(true));
      dispatch(setEnrollmentsError(null));
      const response = await fetchEventEnrollments(requestData);
      dispatch(setEnrollments(response.data));
      dispatch(setTotalEnrollments(response.meta?.totalCount || 0));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event enrollments.";
      dispatch(setEnrollmentsError(message));
    } finally {
      dispatch(setLoadingEnrollments(false));
    }
  };

export const fetchEventWaitlistItemsAction =
  (requestData: FetchEventWaitlistItemsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoadingWaitlistItems(true));
      dispatch(setWaitlistItemsError(null));
      const response = await fetchEventWaitlistItems(requestData);
      dispatch(setWaitlistItems(response.data));
      dispatch(setTotalWaitlistItems(response.meta?.totalCount || 0));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event waitlist items.";
      dispatch(setWaitlistItemsError(message));
    } finally {
      dispatch(setLoadingWaitlistItems(false));
    }
  };

export const fetchWaitlistDetailsAction =
  (requestData: FetchWaitlistDetailsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoadingWaitlistDetails(true));
      dispatch(setWaitlistDetailsError(null));
      const response = await fetchWaitlistDetails(requestData);
      dispatch(setWaitlistDetails(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch waitlist details.";
      dispatch(setWaitlistDetailsError(message));
    } finally {
      dispatch(setLoadingWaitlistDetails(false));
    }
  };

export const moveWaitlistToEnrollmentAction =
  (requestData: MoveWaitlistToEnrollmentRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoadingMoveWaitlist(true));
      dispatch(setErrorMoveWaitlist(null));
      dispatch(setMoveWaitlistResult(null));

      const response: MoveWaitlistToEnrollmentResponse =
        await moveWaitlistToEnrollment(requestData);

      dispatch(setMoveWaitlistResult(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to move waitlist to enrollment.";
      dispatch(setErrorMoveWaitlist(message));
    } finally {
      dispatch(setLoadingMoveWaitlist(false));
    }
  };

export const removeWaitlistItemAction =
  (requestData: RemoveWaitlistItemRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoadingRemoveWaitlist(true));
      dispatch(setErrorRemoveWaitlist(null));
      dispatch(setRemoveWaitlistResult(null));

      const response: RemoveWaitlistItemResponse =
        await removeWaitlistItem(requestData);
      dispatch(setRemoveWaitlistResult(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to remove waitlist item.";
      dispatch(setErrorRemoveWaitlist(message));
    } finally {
      dispatch(setLoadingRemoveWaitlist(false));
    }
  };

export const replaceEnrollmentAttendeeAction =
  (requestData: ReplaceEnrollmentAttendeeRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoadingReplaceAttendee(true));
      dispatch(setErrorReplaceAttendee(null));
      dispatch(setReplaceAttendeeResult(null));

      const response: ReplaceEnrollmentAttendeeResponse =
        await replaceEnrollmentAttendee(requestData);
      dispatch(setReplaceAttendeeResult(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to replace enrollment attendee.";
      dispatch(setErrorReplaceAttendee(message));
    } finally {
      dispatch(setLoadingReplaceAttendee(false));
    }
  };

export const replaceEnrollmentWithEmployeeAction =
  (requestData: ReplaceEnrollmentWithEmployeeRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoadingReplaceWithEmployee(true));
      dispatch(setErrorReplaceWithEmployee(null));
      dispatch(setReplaceWithEmployeeResult(null));

      const response: ReplaceEnrollmentWithEmployeeResponse =
        await replaceEnrollmentWithEmployee(requestData);

      dispatch(setReplaceWithEmployeeResult(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to replace enrollment with employee.";
      dispatch(setErrorReplaceWithEmployee(message));
    } finally {
      dispatch(setLoadingReplaceWithEmployee(false));
    }
  };

export const updateWaitlistPositionAction =
  (
    requestData: UpdateWaitlistPositionRequest,
    onSuccess?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoadingUpdatePosition(true));
      dispatch(setErrorUpdatePosition(null));
      dispatch(setUpdatePositionResult(null));

      dispatch(
        updateWaitlistItemPositionOptimistically({
          waitlistId: requestData.eventWaitlistId,
          newPosition: requestData.newPosition,
        }),
      );

      const response: UpdateWaitlistPositionResponse =
        await updateWaitlistPosition(requestData);
      dispatch(setUpdatePositionResult(response.data));

      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update waitlist position.";
      dispatch(setErrorUpdatePosition(message));

      // Revert by re-fetching
      dispatch(fetchEventWaitlistItemsAction({ uuid: requestData.uuid }));
    } finally {
      dispatch(setLoadingUpdatePosition(false));
    }
  };

export const moveEnrollmentToWaitlistAction =
  (requestData: MoveEnrollmentToWaitlistRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoadingMoveEnrollment(true));
      dispatch(setErrorMoveEnrollment(null));
      dispatch(setMoveEnrollmentResult(null));

      const response: MoveEnrollmentToWaitlistResponse =
        await moveEnrollmentToWaitlist(requestData);

      dispatch(setMoveEnrollmentResult(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to move enrollment to waitlist.";
      dispatch(setErrorMoveEnrollment(message));
    } finally {
      dispatch(setLoadingMoveEnrollment(false));
    }
  };
