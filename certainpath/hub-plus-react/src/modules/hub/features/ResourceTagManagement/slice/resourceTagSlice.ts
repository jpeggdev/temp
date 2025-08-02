import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import {
  CreateResourceTagRequest,
  CreateResourceTagResponse,
} from "@/modules/hub/features/ResourceTagManagement/api/createResourceTag/types";
import { GetEditResourceTagResponse } from "@/modules/hub/features/ResourceTagManagement/api/getEditResourceTag/types";
import {
  EditResourceTagRequest,
  EditResourceTagResponse,
} from "@/modules/hub/features/ResourceTagManagement/api/editResourceTag/types";
import { createResourceTag } from "@/modules/hub/features/ResourceTagManagement/api/createResourceTag/createResourceTagApi";
import { getEditResourceTag } from "@/modules/hub/features/ResourceTagManagement/api/getEditResourceTag/getEditResourceTagApi";
import { editResourceTag } from "@/modules/hub/features/ResourceTagManagement/api/editResourceTag/editResourceTagApi";
import { deleteResourceTag } from "@/modules/hub/features/ResourceTagManagement/api/deleteResourceTag/deleteResourceTagApi";
import {
  GetResourceTagsRequest,
  GetResourceTagsResponse,
} from "@/modules/hub/features/ResourceTagManagement/api/getResourceTags/types";
import { getResourceTags } from "@/modules/hub/features/ResourceTagManagement/api/getResourceTags/getResourceTagsApi";

interface ResourceTagState {
  loadingCreate: boolean;
  createError: string | null;
  createdTag: CreateResourceTagResponse["data"] | null;

  loadingGet: boolean;
  getError: string | null;
  fetchedTag: GetEditResourceTagResponse["data"] | null;

  loadingUpdate: boolean;
  updateError: string | null;
  updatedTag: EditResourceTagResponse["data"] | null;

  loadingDelete: boolean;
  deleteError: string | null;
  deletedTagId: number | null;

  loadingList: boolean;
  listError: string | null;
  tagsList: { id: number; name: string }[];
  totalCount: number;
}

const initialState: ResourceTagState = {
  loadingCreate: false,
  createError: null,
  createdTag: null,

  loadingGet: false,
  getError: null,
  fetchedTag: null,

  loadingUpdate: false,
  updateError: null,
  updatedTag: null,

  loadingDelete: false,
  deleteError: null,
  deletedTagId: null,

  loadingList: false,
  listError: null,
  tagsList: [],
  totalCount: 0,
};

const resourceTagSlice = createSlice({
  name: "resourceTag",
  initialState,
  reducers: {
    setLoadingCreate(state, action: PayloadAction<boolean>) {
      state.loadingCreate = action.payload;
    },
    setCreateError(state, action: PayloadAction<string | null>) {
      state.createError = action.payload;
    },
    setCreatedTag(
      state,
      action: PayloadAction<CreateResourceTagResponse["data"] | null>,
    ) {
      state.createdTag = action.payload;
    },

    setLoadingGet(state, action: PayloadAction<boolean>) {
      state.loadingGet = action.payload;
    },
    setGetError(state, action: PayloadAction<string | null>) {
      state.getError = action.payload;
    },
    setFetchedTag(
      state,
      action: PayloadAction<GetEditResourceTagResponse["data"] | null>,
    ) {
      state.fetchedTag = action.payload;
    },

    setLoadingUpdate(state, action: PayloadAction<boolean>) {
      state.loadingUpdate = action.payload;
    },
    setUpdateError(state, action: PayloadAction<string | null>) {
      state.updateError = action.payload;
    },
    setUpdatedTag(
      state,
      action: PayloadAction<EditResourceTagResponse["data"] | null>,
    ) {
      state.updatedTag = action.payload;
    },

    setLoadingDelete(state, action: PayloadAction<boolean>) {
      state.loadingDelete = action.payload;
    },
    setDeleteError(state, action: PayloadAction<string | null>) {
      state.deleteError = action.payload;
    },
    setDeletedTagId(state, action: PayloadAction<number | null>) {
      state.deletedTagId = action.payload;
    },

    setLoadingList(state, action: PayloadAction<boolean>) {
      state.loadingList = action.payload;
    },
    setListError(state, action: PayloadAction<string | null>) {
      state.listError = action.payload;
    },
    setTagsList(state, action: PayloadAction<{ id: number; name: string }[]>) {
      state.tagsList = action.payload;
    },
    setTotalCount(state, action: PayloadAction<number>) {
      state.totalCount = action.payload;
    },

    resetResourceTagState(state) {
      state.loadingCreate = false;
      state.createError = null;
      state.createdTag = null;

      state.loadingGet = false;
      state.getError = null;
      state.fetchedTag = null;

      state.loadingUpdate = false;
      state.updateError = null;
      state.updatedTag = null;

      state.loadingDelete = false;
      state.deleteError = null;
      state.deletedTagId = null;

      state.loadingList = false;
      state.listError = null;
      state.tagsList = [];
      state.totalCount = 0;
    },
  },
});

export const {
  setLoadingCreate,
  setCreateError,
  setCreatedTag,

  setLoadingGet,
  setGetError,
  setFetchedTag,

  setLoadingUpdate,
  setUpdateError,
  setUpdatedTag,

  setLoadingDelete,
  setDeleteError,
  setDeletedTagId,

  setLoadingList,
  setListError,
  setTagsList,
  setTotalCount,

  resetResourceTagState,
} = resourceTagSlice.actions;

export default resourceTagSlice.reducer;

export const createResourceTagAction =
  (
    requestData: CreateResourceTagRequest,
    onSuccess?: (createdData: CreateResourceTagResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingCreate(true));
    dispatch(setCreateError(null));
    try {
      const response = await createResourceTag(requestData);
      dispatch(setCreatedTag(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to create resource tag.";
      dispatch(setCreateError(message));
    } finally {
      dispatch(setLoadingCreate(false));
    }
  };

export const getEditResourceTagAction =
  (id: number): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingGet(true));
    dispatch(setGetError(null));
    try {
      const response = await getEditResourceTag(id);
      dispatch(setFetchedTag(response.data));
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch resource tag.";
      dispatch(setGetError(message));
    } finally {
      dispatch(setLoadingGet(false));
    }
  };

export const editResourceTagAction =
  (
    id: number,
    requestData: EditResourceTagRequest,
    onSuccess?: (updatedData: EditResourceTagResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingUpdate(true));
    dispatch(setUpdateError(null));
    try {
      const response = await editResourceTag(id, requestData);
      dispatch(setUpdatedTag(response.data));
      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Failed to edit resource tag.";
      dispatch(setUpdateError(message));
    } finally {
      dispatch(setLoadingUpdate(false));
    }
  };

export const deleteResourceTagAction =
  (tagId: number, onSuccess?: () => void): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingDelete(true));
    dispatch(setDeleteError(null));
    try {
      await deleteResourceTag(tagId);
      dispatch(setDeletedTagId(tagId));
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to delete resource tag.";
      dispatch(setDeleteError(message));
    } finally {
      dispatch(setLoadingDelete(false));
    }
  };

export const getResourceTagsAction =
  (
    queryParams: GetResourceTagsRequest,
    onSuccess?: (data: GetResourceTagsResponse["data"]) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    dispatch(setLoadingList(true));
    dispatch(setListError(null));
    try {
      const response = await getResourceTags(queryParams);

      const { tags } = response.data;
      const totalCount = response.meta?.totalCount ?? 0;

      dispatch(setTagsList(tags));
      dispatch(setTotalCount(totalCount));

      if (onSuccess) {
        onSuccess(response.data);
      }
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Failed to fetch resource tags.";
      dispatch(setListError(message));
    } finally {
      dispatch(setLoadingList(false));
    }
  };
