import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateEventCategoryRequest,
  CreateEventCategoryResponse,
} from "@/api/createEventCategory/types";
import { GetEditEventCategoryResponse } from "@/api/getEditEventCategory/types";
import {
  EditEventCategoryDTO,
  EditEventCategoryResponse,
} from "@/api/editEventCategory/types";
import {
  ApiEventCategory,
  FetchEventCategoriesRequest,
} from "@/api/fetchEventCategories/types";
import { createEventCategory } from "@/api/createEventCategory/createEventCategoryApi";
import { getEditEventCategory } from "@/api/getEditEventCategory/getEditEventCategoryApi";
import { editEventCategory } from "@/api/editEventCategory/editEventCategoryApi";
import { deleteEventCategory } from "@/api/deleteEventCategory/deleteEventCategoryApi";
import { fetchEventCategories } from "@/api/fetchEventCategories/fetchEventCategoriesApi";

interface EventCategorySliceState {
  loadingCreate: boolean;
  createError: string | null;
  createdCategory: CreateEventCategoryResponse["data"] | null;

  loadingGet: boolean;
  getError: string | null;
  fetchedCategory: GetEditEventCategoryResponse["data"] | null;

  loadingUpdate: boolean;
  updateError: string | null;
  updatedCategory: EditEventCategoryResponse["data"] | null;

  loadingDelete: boolean;
  deleteError: string | null;
  deletedCategoryId: number | null;

  loadingList: boolean;
  listError: string | null;
  categoriesList: ApiEventCategory[];
  totalCount: number;
}

const initialState: EventCategorySliceState = {
  loadingCreate: false,
  createError: null,
  createdCategory: null,

  loadingGet: false,
  getError: null,
  fetchedCategory: null,

  loadingUpdate: false,
  updateError: null,
  updatedCategory: null,

  loadingDelete: false,
  deleteError: null,
  deletedCategoryId: null,

  loadingList: false,
  listError: null,
  categoriesList: [],
  totalCount: 0,
};

const eventCategorySlice = createSlice({
  name: "eventCategory",
  initialState,
  reducers: {
    setLoadingCreate(state, action: PayloadAction<boolean>) {
      state.loadingCreate = action.payload;
    },
    setCreateError(state, action: PayloadAction<string | null>) {
      state.createError = action.payload;
    },
    setCreatedCategory(
      state,
      action: PayloadAction<CreateEventCategoryResponse["data"] | null>,
    ) {
      state.createdCategory = action.payload;
    },

    setLoadingGet(state, action: PayloadAction<boolean>) {
      state.loadingGet = action.payload;
    },
    setGetError(state, action: PayloadAction<string | null>) {
      state.getError = action.payload;
    },
    setFetchedCategory(
      state,
      action: PayloadAction<GetEditEventCategoryResponse["data"] | null>,
    ) {
      state.fetchedCategory = action.payload;
    },

    setLoadingUpdate(state, action: PayloadAction<boolean>) {
      state.loadingUpdate = action.payload;
    },
    setUpdateError(state, action: PayloadAction<string | null>) {
      state.updateError = action.payload;
    },
    setUpdatedCategory(
      state,
      action: PayloadAction<EditEventCategoryResponse["data"] | null>,
    ) {
      state.updatedCategory = action.payload;
    },

    setLoadingDelete(state, action: PayloadAction<boolean>) {
      state.loadingDelete = action.payload;
    },
    setDeleteError(state, action: PayloadAction<string | null>) {
      state.deleteError = action.payload;
    },
    setDeletedCategoryId(state, action: PayloadAction<number | null>) {
      state.deletedCategoryId = action.payload;
    },

    setLoadingList(state, action: PayloadAction<boolean>) {
      state.loadingList = action.payload;
    },
    setListError(state, action: PayloadAction<string | null>) {
      state.listError = action.payload;
    },
    setCategoriesList(state, action: PayloadAction<ApiEventCategory[]>) {
      state.categoriesList = action.payload;
    },
    setTotalCount(state, action: PayloadAction<number>) {
      state.totalCount = action.payload;
    },

    resetEventCategoryState(state) {
      state.loadingCreate = false;
      state.createError = null;
      state.createdCategory = null;

      state.loadingGet = false;
      state.getError = null;
      state.fetchedCategory = null;

      state.loadingUpdate = false;
      state.updateError = null;
      state.updatedCategory = null;

      state.loadingDelete = false;
      state.deleteError = null;
      state.deletedCategoryId = null;

      state.loadingList = false;
      state.listError = null;
      state.categoriesList = [];
      state.totalCount = 0;
    },
  },
});

export const {
  setLoadingCreate,
  setCreateError,
  setCreatedCategory,

  setLoadingGet,
  setGetError,
  setFetchedCategory,

  setLoadingUpdate,
  setUpdateError,
  setUpdatedCategory,

  setLoadingDelete,
  setDeleteError,
  setDeletedCategoryId,

  setLoadingList,
  setListError,
  setCategoriesList,
  setTotalCount,

  resetEventCategoryState,
} = eventCategorySlice.actions;

export default eventCategorySlice.reducer;

export const createEventCategoryAction =
  (
    requestData: CreateEventCategoryRequest,
    onSuccess?: (createdData: CreateEventCategoryResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setCreateError(null));

    try {
      const response = await createEventCategory(requestData);
      dispatch(setCreatedCategory(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to create event category.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const getEditEventCategoryAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingGet(true));
    dispatch(setGetError(null));

    try {
      const response = await getEditEventCategory(id);
      dispatch(setFetchedCategory(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event category.";
      dispatch(setGetError(message));
    } finally {
      dispatch(setLoadingGet(false));
    }
  };

export const editEventCategoryAction =
  (
    id: number,
    editEventCategoryDTO: EditEventCategoryDTO,
    onSuccess?: (updatedData: EditEventCategoryResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setUpdateError(null));

    try {
      const response = await editEventCategory(id, editEventCategoryDTO);
      dispatch(setUpdatedCategory(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update event category.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const deleteEventCategoryAction =
  (id: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDelete(true));
    dispatch(setDeleteError(null));

    try {
      const response = await deleteEventCategory(id);
      dispatch(setDeletedCategoryId(response.data.id));
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to delete event category.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setLoadingDelete(false));
    }
  };

export const fetchEventCategoriesAction =
  (
    requestData: FetchEventCategoriesRequest,
    onSuccess?: (categories: ApiEventCategory[]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingList(true));
    dispatch(setListError(null));

    try {
      const response = await fetchEventCategories(requestData);
      const { data: categoriesArray, meta } = response;
      const { totalCount } = meta;
      dispatch(setCategoriesList(categoriesArray));
      dispatch(setTotalCount(totalCount));

      if (onSuccess) {
        onSuccess(categoriesArray);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch event categories.";
      dispatch(setListError(message));
    } finally {
      dispatch(setLoadingList(false));
    }
  };
