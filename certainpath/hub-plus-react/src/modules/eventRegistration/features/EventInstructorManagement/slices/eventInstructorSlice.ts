import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  SearchEventInstructorItem,
  SearchEventInstructorsRequest,
  SearchEventInstructorsResponse,
} from "@/modules/eventRegistration/features/EventInstructorManagement/api/searchEventInstructors/types";
import {
  FetchedEventInstructorData,
  GetEventInstructorResponse,
} from "@/modules/eventRegistration/features/EventInstructorManagement/api/getEventInstructor/types";
import {
  CreatedEventInstructorData,
  CreateEventInstructorRequest,
  CreateEventInstructorResponse,
} from "@/modules/eventRegistration/features/EventInstructorManagement/api/createEventInstructor/types";
import {
  UpdatedEventInstructorData,
  UpdateEventInstructorRequest,
  UpdateEventInstructorResponse,
} from "@/modules/eventRegistration/features/EventInstructorManagement/api/updateEventInstructor/types";
import { searchEventInstructors } from "@/modules/eventRegistration/features/EventInstructorManagement/api/searchEventInstructors/searchEventInstructorsApi";
import { createEventInstructor } from "@/modules/eventRegistration/features/EventInstructorManagement/api/createEventInstructor/createEventInstructorApi";
import { updateEventInstructor } from "@/modules/eventRegistration/features/EventInstructorManagement/api/updateEventInstructor/updateEventInstructorApi";
import { deleteEventInstructor } from "@/modules/eventRegistration/features/EventInstructorManagement/api/deleteEventInstructor/deleteEventInstructorApi";
import { getEventInstructor } from "@/modules/eventRegistration/features/EventInstructorManagement/api/getEventInstructor/getEventInstructorApi";

interface EventInstructorSliceState {
  instructors: SearchEventInstructorItem[];
  totalCount: number;
  searchLoading: boolean;
  searchError: string | null;
  createLoading: boolean;
  createError: string | null;
  updateLoading: boolean;
  updateError: string | null;
  deleteLoading: boolean;
  deleteError: string | null;
  detailsLoading: boolean;
  detailsError: string | null;
  selectedInstructor: FetchedEventInstructorData | null;
}

const initialState: EventInstructorSliceState = {
  instructors: [],
  totalCount: 0,
  searchLoading: false,
  searchError: null,
  createLoading: false,
  createError: null,
  updateLoading: false,
  updateError: null,
  deleteLoading: false,
  deleteError: null,
  detailsLoading: false,
  detailsError: null,
  selectedInstructor: null,
};

const eventInstructorSlice = createSlice({
  name: "eventInstructor",
  initialState,
  reducers: {
    setSearchLoading(state, action: PayloadAction<boolean>) {
      state.searchLoading = action.payload;
    },
    setSearchError(state, action: PayloadAction<string | null>) {
      state.searchError = action.payload;
    },
    setInstructors(state, action: PayloadAction<SearchEventInstructorItem[]>) {
      state.instructors = action.payload;
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
    addCreatedInstructor(
      state,
      action: PayloadAction<CreatedEventInstructorData>,
    ) {
      if (action.payload.id != null && action.payload.name != null) {
        state.instructors.push({
          id: action.payload.id,
          name: action.payload.name,
          email: action.payload.email,
          phone: action.payload.phone,
        });
        state.totalCount += 1;
      }
    },
    setUpdateLoading(state, action: PayloadAction<boolean>) {
      state.updateLoading = action.payload;
    },
    setUpdateError(state, action: PayloadAction<string | null>) {
      state.updateError = action.payload;
    },
    updateInstructorInList(
      state,
      action: PayloadAction<UpdatedEventInstructorData>,
    ) {
      const { id, name, email, phone } = action.payload;
      const index = state.instructors.findIndex((i) => i.id === id);
      if (index !== -1) {
        state.instructors[index] = { id, name, email, phone };
      }
    },
    setDeleteLoading(state, action: PayloadAction<boolean>) {
      state.deleteLoading = action.payload;
    },
    setDeleteError(state, action: PayloadAction<string | null>) {
      state.deleteError = action.payload;
    },
    removeInstructor(state, action: PayloadAction<number>) {
      state.instructors = state.instructors.filter(
        (instructor) => instructor.id !== action.payload,
      );
      if (state.totalCount > 0) {
        state.totalCount -= 1;
      }
    },
    setDetailsLoading(state, action: PayloadAction<boolean>) {
      state.detailsLoading = action.payload;
    },
    setDetailsError(state, action: PayloadAction<string | null>) {
      state.detailsError = action.payload;
    },
    setSelectedInstructor(
      state,
      action: PayloadAction<FetchedEventInstructorData | null>,
    ) {
      state.selectedInstructor = action.payload;
    },
  },
});

export default eventInstructorSlice.reducer;

export const {
  setSearchLoading,
  setSearchError,
  setInstructors,
  setTotalCount,
  setCreateLoading,
  setCreateError,
  addCreatedInstructor,
  setUpdateLoading,
  setUpdateError,
  updateInstructorInList,
  setDeleteLoading,
  setDeleteError,
  removeInstructor,
  setDetailsLoading,
  setDetailsError,
  setSelectedInstructor,
} = eventInstructorSlice.actions;

export const searchEventInstructorsAction =
  (params: SearchEventInstructorsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setSearchLoading(true));
    dispatch(setSearchError(null));
    try {
      const response: SearchEventInstructorsResponse =
        await searchEventInstructors(params);
      dispatch(setInstructors(response.data.instructors));
      dispatch(
        setTotalCount(
          response.data.totalCount || response.meta?.totalCount || 0,
        ),
      );
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to search event instructors.";
      dispatch(setSearchError(message));
    } finally {
      dispatch(setSearchLoading(false));
    }
  };

export const createEventInstructorAction =
  (
    instructorData: CreateEventInstructorRequest,
    onSuccess?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setCreateLoading(true));
    dispatch(setCreateError(null));
    try {
      const response: CreateEventInstructorResponse =
        await createEventInstructor(instructorData);
      dispatch(addCreatedInstructor(response.data));
      onSuccess?.();
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to create event instructor.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setCreateLoading(false));
    }
  };

export const updateEventInstructorAction =
  (
    id: number,
    instructorData: UpdateEventInstructorRequest,
    onSuccess?: () => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setUpdateLoading(true));
    dispatch(setUpdateError(null));
    try {
      const response: UpdateEventInstructorResponse =
        await updateEventInstructor(id, instructorData);
      dispatch(updateInstructorInList(response.data));
      onSuccess?.();
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update event instructor.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setUpdateLoading(false));
    }
  };

export const deleteEventInstructorAction =
  (id: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDeleteLoading(true));
    dispatch(setDeleteError(null));
    try {
      await deleteEventInstructor(id);
      dispatch(removeInstructor(id));
      onSuccess?.();
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to delete event instructor.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setDeleteLoading(false));
    }
  };

export const getEventInstructorAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDetailsLoading(true));
    dispatch(setDetailsError(null));
    try {
      const response: GetEventInstructorResponse = await getEventInstructor(id);
      dispatch(setSelectedInstructor(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to get event instructor details.";
      dispatch(setDetailsError(message));
    } finally {
      dispatch(setDetailsLoading(false));
    }
  };
