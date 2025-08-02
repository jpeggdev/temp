import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateEmailTemplateCategoryRequest,
  CreateEmailTemplateCategoryResponse,
} from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/api/createEmailTemplateCategory/types";
import { GetEditEmailTemplateCategoryResponse } from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/api/getEditEmailTemplateCategory/types";
import {
  UpdateEmailTemplateCategoryRequest,
  UpdateEmailTemplateCategoryResponse,
} from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/api/updateEmailTemplateCategory/types";
import {
  EmailTemplateCategory,
  fetchEmailTemplateCategoriesRequest,
} from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplateCategories/types";
import { createEmailTemplateCategory } from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/api/createEmailTemplateCategory/createEmailTemplateCategoryApi";
import { getEditEmailTemplateCategory } from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/api/getEditEmailTemplateCategory/getEditEmailTemplateCategoryApi";
import { updateEmailTemplateCategory } from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/api/updateEmailTemplateCategory/updateEmailTemplateCategoryApi";
import { deleteEmailTemplateCategory } from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/api/deleteEmailTemplateCategory/deleteEmailTemplateCategoryApi";
import { fetchEmailTemplateCategories } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplateCategories/fetchEmailTemplateCategoriesApi";

interface EmailTemplateCategoryState {
  loadingCreate: boolean;
  createError: string | null;
  createdCategory: CreateEmailTemplateCategoryResponse["data"] | null;

  loadingGet: boolean;
  getError: string | null;
  fetchedCategory: GetEditEmailTemplateCategoryResponse["data"] | null;

  loadingUpdate: boolean;
  updateError: string | null;
  updatedCategory: UpdateEmailTemplateCategoryResponse["data"] | null;

  loadingDelete: boolean;
  deleteError: string | null;
  deletedCategoryId: number | null;

  loadingList: boolean;
  listError: string | null;
  categoriesList: EmailTemplateCategory[];
  totalCount: number;
}

const initialState: EmailTemplateCategoryState = {
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

const emailTemplateCategorySlice = createSlice({
  name: "emailTemplateCategory",
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
      action: PayloadAction<CreateEmailTemplateCategoryResponse["data"] | null>,
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
      action: PayloadAction<
        GetEditEmailTemplateCategoryResponse["data"] | null
      >,
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
      action: PayloadAction<UpdateEmailTemplateCategoryResponse["data"] | null>,
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
    setCategoriesList(state, action: PayloadAction<EmailTemplateCategory[]>) {
      state.categoriesList = action.payload;
    },
    setTotalCount(state, action: PayloadAction<number>) {
      state.totalCount = action.payload;
    },

    resetEmailTemplateCategoryState(state) {
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

  resetEmailTemplateCategoryState,
} = emailTemplateCategorySlice.actions;

export default emailTemplateCategorySlice.reducer;

export const createEmailTemplateCategoryAction =
  (
    requestData: CreateEmailTemplateCategoryRequest,
    onSuccess?: (
      createdData: CreateEmailTemplateCategoryResponse["data"],
    ) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setCreateError(null));
    try {
      const response = await createEmailTemplateCategory(requestData);
      dispatch(setCreatedCategory(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to create email template category.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const getEditEmailTemplateCategoryAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingGet(true));
    dispatch(setGetError(null));
    try {
      const response = await getEditEmailTemplateCategory(id);
      dispatch(setFetchedCategory(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch email template category.";
      dispatch(setGetError(message));
    } finally {
      dispatch(setLoadingGet(false));
    }
  };

export const updateEmailTemplateCategoryAction =
  (
    id: number,
    requestData: UpdateEmailTemplateCategoryRequest,
    onSuccess?: (
      updatedData: UpdateEmailTemplateCategoryResponse["data"],
    ) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setUpdateError(null));
    try {
      const response = await updateEmailTemplateCategory(id, requestData);
      dispatch(setUpdatedCategory(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to update email template category.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const deleteEmailTemplateCategoryAction =
  (id: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDelete(true));
    dispatch(setDeleteError(null));
    try {
      await deleteEmailTemplateCategory(id);
      dispatch(setDeletedCategoryId(id));
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to delete email template category.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setLoadingDelete(false));
    }
  };

export const fetchEmailTemplateCategoriesAction =
  (
    queryParams: fetchEmailTemplateCategoriesRequest,
    onSuccess?: (data: EmailTemplateCategory[]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingList(true));
    dispatch(setListError(null));
    try {
      const response = await fetchEmailTemplateCategories(queryParams);
      const categories = response.data;
      const totalCount = response.meta?.totalCount ?? 0;

      dispatch(setCategoriesList(categories));
      dispatch(setTotalCount(totalCount));

      if (onSuccess) {
        onSuccess(categories);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch email template categories.";
      dispatch(setListError(message));
    } finally {
      dispatch(setLoadingList(false));
    }
  };
