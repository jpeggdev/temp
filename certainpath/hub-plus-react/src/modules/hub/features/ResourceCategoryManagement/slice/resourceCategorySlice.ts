import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateResourceCategoryRequest,
  CreateResourceCategoryResponse,
} from "@/modules/hub/features/ResourceCategoryManagement/api/createResourceCategory/types";
import { GetEditResourceCategoryResponse } from "@/modules/hub/features/ResourceCategoryManagement/api/getEditResourceCategory/types";
import {
  EditResourceCategoryRequest,
  EditResourceCategoryResponse,
} from "@/modules/hub/features/ResourceCategoryManagement/api/editResourceCategory/types";
import { createResourceCategory } from "@/modules/hub/features/ResourceCategoryManagement/api/createResourceCategory/createResourceCategoryApi";
import { getEditResourceCategory } from "@/modules/hub/features/ResourceCategoryManagement/api/getEditResourceCategory/getEditResourceCategoryApi";
import { editResourceCategory } from "@/modules/hub/features/ResourceCategoryManagement/api/editResourceCategory/editResourceCategoryApi";
import { deleteResourceCategory } from "@/modules/hub/features/ResourceCategoryManagement/api/deleteResourceCategory/deleteResourceCategoryApi";
import {
  GetResourceCategoriesRequest,
  GetResourceCategoriesResponse,
} from "@/modules/hub/features/ResourceCategoryManagement/api/getResourceCategories/types";
import { getResourceCategories } from "@/modules/hub/features/ResourceCategoryManagement/api/getResourceCategories/getResourceCategoriesApi";

interface ResourceCategoriesState {
  loadingCreate: boolean;
  createError: string | null;
  createdCategory: CreateResourceCategoryResponse["data"] | null;

  loadingGet: boolean;
  getError: string | null;
  fetchedCategory: GetEditResourceCategoryResponse["data"] | null;

  loadingUpdate: boolean;
  updateError: string | null;
  updatedCategory: EditResourceCategoryResponse["data"] | null;

  loadingDelete: boolean;
  deleteError: string | null;
  deletedCategoryId: number | null;

  loadingList: boolean;
  listError: string | null;
  categoriesList: { id: number; name: string }[];
  totalCount: number;
}

const initialState: ResourceCategoriesState = {
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

const resourceCategorySlice = createSlice({
  name: "resourceCategories",
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
      action: PayloadAction<CreateResourceCategoryResponse["data"] | null>,
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
      action: PayloadAction<GetEditResourceCategoryResponse["data"] | null>,
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
      action: PayloadAction<EditResourceCategoryResponse["data"] | null>,
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
    setCategoriesList(
      state,
      action: PayloadAction<{ id: number; name: string }[]>,
    ) {
      state.categoriesList = action.payload;
    },
    setTotalCount(state, action: PayloadAction<number>) {
      state.totalCount = action.payload;
    },

    resetResourceCategoriesState(state) {
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

  resetResourceCategoriesState,
} = resourceCategorySlice.actions;

export default resourceCategorySlice.reducer;

export const createResourceCategoryAction =
  (
    requestData: CreateResourceCategoryRequest,
    onSuccess?: (createdData: CreateResourceCategoryResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setCreateError(null));
    try {
      const response = await createResourceCategory(requestData);
      dispatch(setCreatedCategory(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to create resource category.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const getEditResourceCategoryAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingGet(true));
    dispatch(setGetError(null));
    try {
      const response = await getEditResourceCategory(id);
      dispatch(setFetchedCategory(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch resource category.";
      dispatch(setGetError(message));
    } finally {
      dispatch(setLoadingGet(false));
    }
  };

export const editResourceCategoryAction =
  (
    id: number,
    requestData: EditResourceCategoryRequest,
    onSuccess?: (updatedData: EditResourceCategoryResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setUpdateError(null));
    try {
      const response = await editResourceCategory(id, requestData);
      dispatch(setUpdatedCategory(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to edit resource category.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const deleteResourceCategoryAction =
  (categoryId: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDelete(true));
    dispatch(setDeleteError(null));
    try {
      await deleteResourceCategory(categoryId);
      dispatch(setDeletedCategoryId(categoryId));
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to delete resource category.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setLoadingDelete(false));
    }
  };

export const getResourceCategoriesAction =
  (
    queryParams: GetResourceCategoriesRequest,
    onSuccess?: (resData: GetResourceCategoriesResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingList(true));
    dispatch(setListError(null));
    try {
      const response = await getResourceCategories(queryParams);
      const { categories } = response.data;
      const totalCount = response.meta?.totalCount ?? 0;

      dispatch(setCategoriesList(categories));
      dispatch(setTotalCount(totalCount));

      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch resource categories.";
      dispatch(setListError(message));
    } finally {
      dispatch(setLoadingList(false));
    }
  };
