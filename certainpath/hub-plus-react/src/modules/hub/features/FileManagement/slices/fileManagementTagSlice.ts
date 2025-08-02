// src/modules/hub/features/FileManagement/slices/fileManagementTagSlice.ts

import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunkGeneric } from "@/app/store";

import { assignTagToNode } from "../api/assignTagToNode/assignTagToNodeApi";
import { createAndAssignTag } from "../api/createAndAssignTag/createAndAssignTagApi";
import { deleteTag } from "../api/deleteTag/deleteTagApi";
import { listTags } from "../api/listTags/listTagsApi";
import { removeTagFromNode } from "../api/removeTagFromNode/removeTagFromNodeApi";
import { renameTag } from "../api/renameTag/renameTagApi";
import {
  AssignTagToNodeRequest,
  AssignTagToNodeData,
  AssignTagToNodeResponse,
} from "../api/assignTagToNode/types";
import {
  CreateAndAssignTagRequest,
  CreateAndAssignTagData,
  CreateAndAssignTagResponse,
} from "../api/createAndAssignTag/types";
import { TagSummary } from "../api/listTags/types";
import {
  RemoveTagFromNodeRequest,
  RemoveTagFromNodeResponse,
} from "../api/removeTagFromNode/types";
import { RenameTagRequest, RenameTagData } from "../api/renameTag/types";

interface FileManagementTagState {
  listTagsLoading: boolean;
  listTagsError: string | null;
  tags: TagSummary[];
  tagsTotalCount: number;
  assignTagLoading: boolean;
  assignTagError: string | null;
  assignedTag: AssignTagToNodeData | null;
  createTagLoading: boolean;
  createTagError: string | null;
  createdTag: CreateAndAssignTagData | null;
  deleteTagLoading: boolean;
  deleteTagError: string | null;
  removeTagLoading: boolean;
  removeTagError: string | null;
  renameTagLoading: boolean;
  renameTagError: string | null;
  renamedTag: RenameTagData | null;
}

const initialState: FileManagementTagState = {
  listTagsLoading: false,
  listTagsError: null,
  tags: [],
  tagsTotalCount: 0,
  assignTagLoading: false,
  assignTagError: null,
  assignedTag: null,
  createTagLoading: false,
  createTagError: null,
  createdTag: null,
  deleteTagLoading: false,
  deleteTagError: null,
  removeTagLoading: false,
  removeTagError: null,
  renameTagLoading: false,
  renameTagError: null,
  renamedTag: null,
};

export const fileManagementTagSlice = createSlice({
  name: "fileManagementTag",
  initialState,
  reducers: {
    setListTagsLoading(state, action: PayloadAction<boolean>) {
      state.listTagsLoading = action.payload;
    },
    setListTagsError(state, action: PayloadAction<string | null>) {
      state.listTagsError = action.payload;
    },
    setTags(state, action: PayloadAction<TagSummary[]>) {
      state.tags = action.payload;
    },
    setTagsTotalCount(state, action: PayloadAction<number>) {
      state.tagsTotalCount = action.payload;
    },
    setAssignTagLoading(state, action: PayloadAction<boolean>) {
      state.assignTagLoading = action.payload;
    },
    setAssignTagError(state, action: PayloadAction<string | null>) {
      state.assignTagError = action.payload;
    },
    setAssignedTag(state, action: PayloadAction<AssignTagToNodeData | null>) {
      state.assignedTag = action.payload;
    },
    setCreateTagLoading(state, action: PayloadAction<boolean>) {
      state.createTagLoading = action.payload;
    },
    setCreateTagError(state, action: PayloadAction<string | null>) {
      state.createTagError = action.payload;
    },
    setCreatedTag(state, action: PayloadAction<CreateAndAssignTagData | null>) {
      state.createdTag = action.payload;
    },
    setDeleteTagLoading(state, action: PayloadAction<boolean>) {
      state.deleteTagLoading = action.payload;
    },
    setDeleteTagError(state, action: PayloadAction<string | null>) {
      state.deleteTagError = action.payload;
    },
    setRemoveTagLoading(state, action: PayloadAction<boolean>) {
      state.removeTagLoading = action.payload;
    },
    setRemoveTagError(state, action: PayloadAction<string | null>) {
      state.removeTagError = action.payload;
    },
    setRenameTagLoading(state, action: PayloadAction<boolean>) {
      state.renameTagLoading = action.payload;
    },
    setRenameTagError(state, action: PayloadAction<string | null>) {
      state.renameTagError = action.payload;
    },
    setRenamedTag(state, action: PayloadAction<RenameTagData | null>) {
      state.renamedTag = action.payload;
    },
    // New reducer to add a newly created tag to the tags list
    addTagToList(state, action: PayloadAction<CreateAndAssignTagData>) {
      const newTag = action.payload;

      // Check if the tag already exists in the list to avoid duplicates
      const existingTagIndex = state.tags.findIndex(
        (tag) => tag.id === newTag.id,
      );

      if (existingTagIndex === -1) {
        // If tag doesn't exist, add it to the list
        const tagSummary: TagSummary = {
          id: newTag.id,
          name: newTag.name,
          color: newTag.color,
          createdAt: newTag.createdAt,
          updatedAt: newTag.updatedAt,
        };

        state.tags.push(tagSummary);
        // Increment the total count of tags
        state.tagsTotalCount += 1;
      }
    },
  },
});

export const {
  setListTagsLoading,
  setListTagsError,
  setTags,
  setTagsTotalCount,
  setAssignTagLoading,
  setAssignTagError,
  setAssignedTag,
  setCreateTagLoading,
  setCreateTagError,
  setCreatedTag,
  setDeleteTagLoading,
  setDeleteTagError,
  setRemoveTagLoading,
  setRemoveTagError,
  setRenameTagLoading,
  setRenameTagError,
  setRenamedTag,
  addTagToList,
} = fileManagementTagSlice.actions;

export const listTagsAction =
  (): AppThunkGeneric<Promise<any>> => async (dispatch: AppDispatch) => {
    try {
      dispatch(setListTagsLoading(true));
      dispatch(setListTagsError(null));
      const response = await listTags();
      dispatch(setTags(response.data.tags));
      dispatch(setTagsTotalCount(response.data.totalCount));
      return response;
    } catch (error) {
      dispatch(
        setListTagsError(
          error instanceof Error ? error.message : "Failed to load tags.",
        ),
      );
      throw error;
    } finally {
      dispatch(setListTagsLoading(false));
    }
  };

export const assignTagToNodeAction =
  (
    requestData: AssignTagToNodeRequest,
  ): AppThunkGeneric<Promise<AssignTagToNodeResponse>> =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setAssignTagLoading(true));
      dispatch(setAssignTagError(null));
      const response = await assignTagToNode(requestData);
      dispatch(setAssignedTag(response.data));
      return response;
    } catch (error) {
      dispatch(
        setAssignTagError(
          error instanceof Error
            ? error.message
            : "Failed to assign tag to node.",
        ),
      );
      throw error;
    } finally {
      dispatch(setAssignTagLoading(false));
    }
  };

export const createAndAssignTagAction =
  (
    requestData: CreateAndAssignTagRequest,
  ): AppThunkGeneric<Promise<CreateAndAssignTagResponse>> =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setCreateTagLoading(true));
      dispatch(setCreateTagError(null));
      const response = await createAndAssignTag(requestData);

      // Set the created tag in state
      dispatch(setCreatedTag(response.data));

      // Also add the new tag to the tags list
      dispatch(addTagToList(response.data));

      return response;
    } catch (error) {
      dispatch(
        setCreateTagError(
          error instanceof Error
            ? error.message
            : "Failed to create and assign tag.",
        ),
      );
      throw error;
    } finally {
      dispatch(setCreateTagLoading(false));
    }
  };

export const deleteTagAction =
  (id: number): AppThunkGeneric<Promise<boolean>> =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setDeleteTagLoading(true));
      dispatch(setDeleteTagError(null));
      await deleteTag(id);
      return true;
    } catch (error) {
      dispatch(
        setDeleteTagError(
          error instanceof Error ? error.message : "Failed to delete tag.",
        ),
      );
      throw error;
    } finally {
      dispatch(setDeleteTagLoading(false));
    }
  };

export const removeTagFromNodeAction =
  (
    requestData: RemoveTagFromNodeRequest,
  ): AppThunkGeneric<Promise<RemoveTagFromNodeResponse>> =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setRemoveTagLoading(true));
      dispatch(setRemoveTagError(null));
      const response = await removeTagFromNode(requestData);
      return response;
    } catch (error) {
      dispatch(
        setRemoveTagError(
          error instanceof Error
            ? error.message
            : "Failed to remove tag from node.",
        ),
      );
      throw error;
    } finally {
      dispatch(setRemoveTagLoading(false));
    }
  };

export const renameTagAction =
  (id: number, requestData: RenameTagRequest): AppThunkGeneric<Promise<any>> =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setRenameTagLoading(true));
      dispatch(setRenameTagError(null));
      const response = await renameTag(id, requestData);
      dispatch(setRenamedTag(response.data));
      return response;
    } catch (error) {
      dispatch(
        setRenameTagError(
          error instanceof Error ? error.message : "Failed to rename tag.",
        ),
      );
      throw error;
    } finally {
      dispatch(setRenameTagLoading(false));
    }
  };

export default fileManagementTagSlice.reducer;
