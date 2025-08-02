import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  EventTagItem,
  FetchEventTagsRequest,
  FetchEventTagsResponse,
} from "@/modules/eventRegistration/features/EventTagManagement/api/fetchEventTags/types";
import {
  FetchSingleEventTagResponse,
  SingleEventTagData,
} from "@/modules/eventRegistration/features/EventTagManagement/api/fetchSingleEventTag/types";
import {
  CreatedEventTagData,
  CreateEventTagResponse,
} from "@/modules/eventRegistration/features/EventTagManagement/api/createEventTag/types";
import {
  EditedEventTagData,
  EditEventTagResponse,
} from "@/modules/eventRegistration/features/EventTagManagement/api/editEventTag/types";
import { fetchEventTags } from "@/modules/eventRegistration/features/EventTagManagement/api/fetchEventTags/fetchEventTagsApi";
import { createEventTag } from "@/modules/eventRegistration/features/EventTagManagement/api/createEventTag/createEventTagApi";
import { editEventTag } from "@/modules/eventRegistration/features/EventTagManagement/api/editEventTag/editEventTagApi";
import { deleteEventTag } from "@/modules/eventRegistration/features/EventTagManagement/api/deleteEventTag/deleteEventTagApi";
import { fetchSingleEventTag } from "@/modules/eventRegistration/features/EventTagManagement/api/fetchSingleEventTag/fetchSingleEventTagApi";

interface EventTagSliceState {
  tags: EventTagItem[];
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
  singleTag: SingleEventTagData | null;
}

const initialState: EventTagSliceState = {
  tags: [],
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
  singleTag: null,
};

const eventTagSlice = createSlice({
  name: "eventTag",
  initialState,
  reducers: {
    setFetchLoading(state, action: PayloadAction<boolean>) {
      state.fetchLoading = action.payload;
    },
    setFetchError(state, action: PayloadAction<string | null>) {
      state.fetchError = action.payload;
    },
    setTags(state, action: PayloadAction<EventTagItem[]>) {
      state.tags = action.payload;
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
    addCreatedTag(state, action: PayloadAction<CreatedEventTagData>) {
      if (action.payload.id != null && action.payload.name != null) {
        state.tags.push({ id: action.payload.id, name: action.payload.name });
        state.totalCount += 1;
      }
    },
    setEditLoading(state, action: PayloadAction<boolean>) {
      state.editLoading = action.payload;
    },
    setEditError(state, action: PayloadAction<string | null>) {
      state.editError = action.payload;
    },
    updateTag(state, action: PayloadAction<EditedEventTagData>) {
      const { id, name } = action.payload;
      const index = state.tags.findIndex((t) => t.id === id);
      if (index !== -1 && name != null) {
        state.tags[index].name = name;
      }
    },
    setDeleteLoading(state, action: PayloadAction<boolean>) {
      state.deleteLoading = action.payload;
    },
    setDeleteError(state, action: PayloadAction<string | null>) {
      state.deleteError = action.payload;
    },
    removeTag(state, action: PayloadAction<number>) {
      state.tags = state.tags.filter((tag) => tag.id !== action.payload);
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
    setSingleTag(state, action: PayloadAction<SingleEventTagData | null>) {
      state.singleTag = action.payload;
    },
  },
});

export default eventTagSlice.reducer;

export const {
  setFetchLoading,
  setFetchError,
  setTags,
  setTotalCount,
  setCreateLoading,
  setCreateError,
  addCreatedTag,
  setEditLoading,
  setEditError,
  updateTag,
  setDeleteLoading,
  setDeleteError,
  removeTag,
  setSingleLoading,
  setSingleError,
  setSingleTag,
} = eventTagSlice.actions;

export const fetchEventTagsAction =
  (params: FetchEventTagsRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setFetchLoading(true));
    dispatch(setFetchError(null));
    try {
      const response: FetchEventTagsResponse = await fetchEventTags(params);
      dispatch(setTags(response.data.tags));
      dispatch(setTotalCount(response.data.totalCount));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to fetch event tags.";
      dispatch(setFetchError(message));
    } finally {
      dispatch(setFetchLoading(false));
    }
  };

export const createEventTagAction =
  (name: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setCreateLoading(true));
    dispatch(setCreateError(null));
    try {
      const response: CreateEventTagResponse = await createEventTag({ name });
      dispatch(addCreatedTag(response.data));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to create event tag.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setCreateLoading(false));
    }
  };

export const editEventTagAction =
  (id: number, newName: string): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setEditLoading(true));
    dispatch(setEditError(null));
    try {
      const response: EditEventTagResponse = await editEventTag({
        id,
        name: newName,
      });
      dispatch(updateTag(response.data));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to edit event tag.";
      dispatch(setEditError(message));
    } finally {
      dispatch(setEditLoading(false));
    }
  };

export const deleteEventTagAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setDeleteLoading(true));
    dispatch(setDeleteError(null));
    try {
      await deleteEventTag({ id });
      dispatch(removeTag(id));
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to delete event tag.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setDeleteLoading(false));
    }
  };

export const fetchSingleEventTagAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setSingleLoading(true));
    dispatch(setSingleError(null));
    try {
      const response: FetchSingleEventTagResponse = await fetchSingleEventTag({
        id,
      });
      dispatch(setSingleTag(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to get event tag details.";
      dispatch(setSingleError(message));
    } finally {
      dispatch(setSingleLoading(false));
    }
  };
