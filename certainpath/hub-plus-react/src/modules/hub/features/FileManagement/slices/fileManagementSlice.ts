// src/modules/hub/features/FileManagement/slices/fileManagementSlice.ts

import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunk } from "@/app/store";
import { createFolder } from "../api/createFolder/createFolderApi";
import { listFolderContents } from "../api/listFolderContents/listFolderContentsApi";
import { deleteNode } from "../api/deleteNode/deleteNodeApi";
import { renameNode } from "../api/renameNode/renameNodeApi";
import { replaceFile } from "../api/replaceFile/replaceFileApi";
import {
  CreateFolderData,
  CreateFolderRequest,
} from "../api/createFolder/types";
import {
  ListFolderContentsRequestParams,
  FilesystemNode,
  FolderInfo,
  BreadcrumbItem,
} from "../api/listFolderContents/types";
import { RenameNodeRequest, RenameNodeData } from "../api/renameNode/types";
import { Tag } from "../api/getFileSystemNodeDetails/types";
import { ReplaceFileData } from "../api/replaceFile/types";
import { AxiosProgressEvent } from "axios";
import { CreateAndAssignTagData } from "../api/createAndAssignTag/types";
import { AssignTagToNodeData } from "../api/assignTagToNode/types";

// Helper function to sort filesystem nodes
function sortFilesystemNodes(
  nodes: FilesystemNode[],
  sortBy: "name" | "fileType" | "updatedAt" | "fileSize",
  sortOrder: "ASC" | "DESC",
): FilesystemNode[] {
  return [...nodes].sort((a, b) => {
    let comparison = 0;

    // First sort by type (folders first)
    if (a.type !== b.type) {
      return a.type === "folder" ? -1 : 1;
    }

    // Then sort by the selected field
    switch (sortBy) {
      case "name":
        comparison = a.name.localeCompare(b.name);
        break;
      case "fileType":
        comparison = (a.fileType || "").localeCompare(b.fileType || "");
        break;
      case "updatedAt":
        comparison =
          new Date(a.updatedAt).getTime() - new Date(b.updatedAt).getTime();
        break;
      case "fileSize":
        comparison = (a.fileSize || 0) - (b.fileSize || 0);
        break;
    }

    return sortOrder === "ASC" ? comparison : -comparison;
  });
}

// Define backup structure with position information
interface DeletedNodeBackup {
  node: FilesystemNode;
  index: number;
}

interface FileManagementState {
  createFolderLoading: boolean;
  createFolderError: string | null;
  createdFolder: CreateFolderData | null;
  listContentsLoading: boolean;
  listContentsError: string | null;
  folderItems: FilesystemNode[];
  totalCount: number;
  hasMore: boolean;
  nextCursor: string | null;
  currentFolder: FolderInfo | null;
  breadcrumbs: BreadcrumbItem[];
  deleteNodeLoading: boolean;
  deleteNodeError: string | null;
  renameNodeLoading: boolean;
  renameNodeError: string | null;
  renamedNode: RenameNodeData | null;
  searchInput: string;
  sortBy: "name" | "fileType" | "updatedAt" | "fileSize";
  sortOrder: "ASC" | "DESC";
  filterType: string | null;
  selectedItems: string[];
  viewMode: "grid" | "list";
  lastFetchParams: ListFolderContentsRequestParams | null;
  // Added backup for delete operations with position information
  deletedNodeBackup: DeletedNodeBackup | null;
  // Added backup for rename operations
  renamedNodeBackup: FilesystemNode | null;
  // Replace file states
  replaceFileLoading: boolean;
  replaceFileError: string | null;
  replacedFile: ReplaceFileData | null;
}

const initialState: FileManagementState = {
  createFolderLoading: false,
  createFolderError: null,
  createdFolder: null,
  listContentsLoading: false,
  listContentsError: null,
  folderItems: [],
  totalCount: 0,
  hasMore: true,
  nextCursor: null,
  currentFolder: null,
  breadcrumbs: [],
  deleteNodeLoading: false,
  deleteNodeError: null,
  renameNodeLoading: false,
  renameNodeError: null,
  renamedNode: null,
  searchInput: "",
  sortBy: "name",
  sortOrder: "ASC",
  filterType: null,
  selectedItems: [],
  viewMode: "grid",
  lastFetchParams: null,
  // Initialize delete backup
  deletedNodeBackup: null,
  // Initialize rename backup
  renamedNodeBackup: null,
  // Initialize replace file state
  replaceFileLoading: false,
  replaceFileError: null,
  replacedFile: null,
};

export const fileManagementSlice = createSlice({
  name: "fileManagement",
  initialState,
  reducers: {
    // Add a reset action that returns to initial state
    resetFileManagementState() {
      return initialState;
    },
    setCreateFolderLoading(state, action: PayloadAction<boolean>) {
      state.createFolderLoading = action.payload;
    },
    setCreateFolderError(state, action: PayloadAction<string | null>) {
      state.createFolderError = action.payload;
    },
    setCreatedFolder(state, action: PayloadAction<CreateFolderData | null>) {
      state.createdFolder = action.payload;
    },
    setListContentsLoading(state, action: PayloadAction<boolean>) {
      state.listContentsLoading = action.payload;
    },
    setListContentsError(state, action: PayloadAction<string | null>) {
      state.listContentsError = action.payload;
    },
    setFolderItems(state, action: PayloadAction<FilesystemNode[]>) {
      state.folderItems = action.payload;
    },
    appendFolderItems(state, action: PayloadAction<FilesystemNode[]>) {
      // Create a Set of existing UUIDs for O(1) lookup
      const existingUuids = new Set(state.folderItems.map((item) => item.uuid));

      // Only append items that don't already exist in our array
      const newUniqueItems = action.payload.filter(
        (item) => !existingUuids.has(item.uuid),
      );

      state.folderItems = [...state.folderItems, ...newUniqueItems];
    },
    updateNodeInFolderItems(state, action: PayloadAction<FilesystemNode>) {
      const updatedNode = action.payload;
      state.folderItems = state.folderItems.map((item) => {
        if (item.uuid === updatedNode.uuid) {
          return updatedNode;
        } else {
          return item;
        }
      });
    },
    // Update a node's name in state without re-sorting
    updateNodeNameInState(
      state,
      action: PayloadAction<{ uuid: string; name: string }>,
    ) {
      const { uuid, name } = action.payload;
      state.folderItems = state.folderItems.map((item) => {
        if (item.uuid === uuid) {
          return { ...item, name };
        }
        return item;
      });

      // Apply sorting after rename
      state.folderItems = sortFilesystemNodes(
        state.folderItems,
        state.sortBy,
        state.sortOrder,
      );
    },
    // Store a backup of a node before renaming
    storeRenamedNodeBackup(state, action: PayloadAction<FilesystemNode>) {
      state.renamedNodeBackup = action.payload;
    },
    // Restore a node's original name
    restoreRenamedNode(state) {
      if (state.renamedNodeBackup) {
        state.folderItems = state.folderItems.map((item) => {
          if (item.uuid === state.renamedNodeBackup!.uuid) {
            return state.renamedNodeBackup!;
          }
          return item;
        });
        state.renamedNodeBackup = null;
      }
    },
    // New reducer to remove a node from state
    removeNodeFromState(state, action: PayloadAction<string>) {
      const uuid = action.payload;
      state.folderItems = state.folderItems.filter(
        (item) => item.uuid !== uuid,
      );
      // Also remove from selected items if it's there
      if (state.selectedItems.includes(uuid)) {
        state.selectedItems = state.selectedItems.filter((id) => id !== uuid);
      }
    },
    // Store a backup of a node before deletion for recovery, including its position
    storeDeletedNode(state, action: PayloadAction<DeletedNodeBackup>) {
      state.deletedNodeBackup = action.payload;
    },
    // Restore a previously deleted node to its original position
    restoreDeletedNode(state) {
      if (state.deletedNodeBackup) {
        const { node, index } = state.deletedNodeBackup;

        // Ensure the index is within bounds
        const insertIndex = Math.min(index, state.folderItems.length);

        // Create a new array with the item inserted at the original position
        const newFolderItems = [...state.folderItems];
        newFolderItems.splice(insertIndex, 0, node);
        state.folderItems = newFolderItems;

        // Clear the backup
        state.deletedNodeBackup = null;
      }
    },
    // Add uploaded files to state - Mac Finder style (new folders at the very beginning)
    addUploadedFiles(state, action: PayloadAction<FilesystemNode[]>) {
      // For folder organization - keep folders at the top
      // First separate existing folders from files
      const existingFolders = state.folderItems.filter(
        (item) => item.type === "folder",
      );
      const existingFiles = state.folderItems.filter(
        (item) => item.type === "file",
      );

      // Separate new uploads into folders and files
      const newFolders = action.payload.filter(
        (item) => item.type === "folder",
      );
      const newFiles = action.payload.filter((item) => item.type === "file");

      // Combine with new folders at the BEGINNING of all folders
      // Order: new folders, existing folders, new files, existing files
      state.folderItems = [
        ...newFolders, // New folders first
        ...existingFolders, // Then existing folders
        ...newFiles, // Then new files
        ...existingFiles, // Then existing files
      ];

      // Update total count
      state.totalCount += action.payload.length;
    },
    // Update tags for a node
    updateNodeTags(
      state,
      action: PayloadAction<{ nodeUuid: string; tags: Tag[] }>,
    ) {
      const { nodeUuid, tags } = action.payload;
      state.folderItems = state.folderItems.map((item) =>
        item.uuid === nodeUuid ? { ...item, tags } : item,
      );
    },
    // Add a new tag to a node from create and assign
    addTagToNode(state, action: PayloadAction<CreateAndAssignTagData>) {
      const { filesystemNodeUuid, id, name, color } = action.payload;

      // Create the new tag object
      const newTag: Tag = {
        id,
        name,
        color,
      };

      // Update the node in folder items
      state.folderItems = state.folderItems.map((item) => {
        if (item.uuid === filesystemNodeUuid) {
          const existingTags = item.tags || [];
          return {
            ...item,
            tags: [...existingTags, newTag],
          };
        }
        return item;
      });
    },
    // Assign an existing tag to a node
    assignTagToNode(state, action: PayloadAction<AssignTagToNodeData>) {
      const { filesystemNodeUuid, tagId, tagName, tagColor } = action.payload;

      // Create the tag object
      const newTag: Tag = {
        id: tagId,
        name: tagName,
        color: tagColor,
      };

      // Update the node in folder items
      state.folderItems = state.folderItems.map((item) => {
        if (item.uuid === filesystemNodeUuid) {
          const existingTags = item.tags || [];
          // Only add if tag doesn't already exist
          if (!existingTags.some((tag) => tag.id === tagId)) {
            return {
              ...item,
              tags: [...existingTags, newTag],
            };
          }
        }
        return item;
      });
    },
    // Remove a tag from a node
    removeTagFromNode(
      state,
      action: PayloadAction<{ tagId: number; filesystemNodeUuid: string }>,
    ) {
      const { tagId, filesystemNodeUuid } = action.payload;

      // Update the node in folder items
      state.folderItems = state.folderItems.map((item) => {
        if (item.uuid === filesystemNodeUuid && item.tags) {
          return {
            ...item,
            tags: item.tags.filter((tag) => tag.id !== tagId),
          };
        }
        return item;
      });
    },
    setTotalCount(state, action: PayloadAction<number>) {
      state.totalCount = action.payload;
    },
    setHasMore(state, action: PayloadAction<boolean>) {
      state.hasMore = action.payload;
    },
    setNextCursor(state, action: PayloadAction<string | null>) {
      state.nextCursor = action.payload;
    },
    setCurrentFolder(state, action: PayloadAction<FolderInfo | null>) {
      state.currentFolder = action.payload;
    },
    setBreadcrumbs(state, action: PayloadAction<BreadcrumbItem[]>) {
      state.breadcrumbs = action.payload;
    },
    setDeleteNodeLoading(state, action: PayloadAction<boolean>) {
      state.deleteNodeLoading = action.payload;
    },
    setDeleteNodeError(state, action: PayloadAction<string | null>) {
      state.deleteNodeError = action.payload;
    },
    setRenameNodeLoading(state, action: PayloadAction<boolean>) {
      state.renameNodeLoading = action.payload;
    },
    setRenameNodeError(state, action: PayloadAction<string | null>) {
      state.renameNodeError = action.payload;
    },
    setRenamedNode(state, action: PayloadAction<RenameNodeData | null>) {
      state.renamedNode = action.payload;
    },
    setSearchInput(state, action: PayloadAction<string>) {
      state.searchInput = action.payload;
    },
    setSortBy(
      state,
      action: PayloadAction<"name" | "fileType" | "updatedAt" | "fileSize">,
    ) {
      state.sortBy = action.payload;
    },
    setSortOrder(state, action: PayloadAction<"ASC" | "DESC">) {
      state.sortOrder = action.payload;
    },
    setFilterType(state, action: PayloadAction<string | null>) {
      state.filterType = action.payload;
    },
    toggleSelectedItem(state, action: PayloadAction<string>) {
      const uuid = action.payload;
      if (state.selectedItems.includes(uuid)) {
        state.selectedItems = state.selectedItems.filter((id) => id !== uuid);
      } else {
        state.selectedItems.push(uuid);
      }
    },
    clearSelectedItems(state) {
      state.selectedItems = [];
    },
    setViewMode(state, action: PayloadAction<"grid" | "list">) {
      state.viewMode = action.payload;
    },
    setLastFetchParams(
      state,
      action: PayloadAction<ListFolderContentsRequestParams | null>,
    ) {
      state.lastFetchParams = action.payload;
    },
    clearFilters(state) {
      state.searchInput = "";
      state.filterType = null;
      state.sortBy = "name";
      state.sortOrder = "ASC";
    },
    // New actions for replace file
    setReplaceFileLoading(state, action: PayloadAction<boolean>) {
      state.replaceFileLoading = action.payload;
    },
    setReplaceFileError(state, action: PayloadAction<string | null>) {
      state.replaceFileError = action.payload;
    },
    setReplacedFile(state, action: PayloadAction<ReplaceFileData | null>) {
      state.replacedFile = action.payload;
    },
    // Update a file node with replaced data
    updateFileWithReplacedData(state, action: PayloadAction<ReplaceFileData>) {
      const replacedFile = action.payload;
      state.folderItems = state.folderItems.map((item) => {
        if (item.uuid === replacedFile.uuid) {
          // Update the file node with the replaced file data
          return {
            ...item,
            name: replacedFile.name,
            fileType: replacedFile.fileType,
            fileSize: replacedFile.fileSize,
            updatedAt: replacedFile.replacedAt, // Use replacedAt as updatedAt
            // Preserve other fields from the original item
          };
        }
        return item;
      });
    },
    // New reducer to remove multiple nodes from state at once
    removeMultipleNodesFromState(state, action: PayloadAction<string[]>) {
      const uuidsToRemove = action.payload;
      const uuidsSet = new Set(uuidsToRemove);

      // Count how many items we're actually removing (to update the total count correctly)
      const itemsToRemoveCount = state.folderItems.filter((item) =>
        uuidsSet.has(item.uuid),
      ).length;

      // Filter out the nodes with UUIDs in the removal set
      state.folderItems = state.folderItems.filter(
        (item) => !uuidsSet.has(item.uuid),
      );

      // Also remove from selected items if they're there
      state.selectedItems = state.selectedItems.filter(
        (uuid) => !uuidsSet.has(uuid),
      );

      // Update the total count by the actual number removed
      state.totalCount -= itemsToRemoveCount;
    },
  },
});

export const {
  resetFileManagementState,
  setCreateFolderLoading,
  setCreateFolderError,
  setCreatedFolder,
  setListContentsLoading,
  setListContentsError,
  setFolderItems,
  appendFolderItems,
  updateNodeInFolderItems,
  updateNodeNameInState,
  storeRenamedNodeBackup,
  restoreRenamedNode,
  removeNodeFromState,
  storeDeletedNode,
  restoreDeletedNode,
  addUploadedFiles,
  updateNodeTags,
  addTagToNode,
  assignTagToNode,
  removeTagFromNode,
  setTotalCount,
  setHasMore,
  setNextCursor,
  setCurrentFolder,
  setBreadcrumbs,
  setDeleteNodeLoading,
  setDeleteNodeError,
  setRenameNodeLoading,
  setRenameNodeError,
  setRenamedNode,
  setSearchInput,
  setSortBy,
  setSortOrder,
  setFilterType,
  toggleSelectedItem,
  clearSelectedItems,
  setViewMode,
  setLastFetchParams,
  clearFilters,
  // Export actions for replace file
  setReplaceFileLoading,
  setReplaceFileError,
  setReplacedFile,
  updateFileWithReplacedData,
  removeMultipleNodesFromState,
} = fileManagementSlice.actions;

export const createFolderAction =
  (requestData: CreateFolderRequest): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setCreateFolderLoading(true));
      dispatch(setCreateFolderError(null));
      const response = await createFolder(requestData);

      // Store the created folder data
      dispatch(setCreatedFolder(response.data));

      // Convert the created folder to a FilesystemNode format
      const folderData = response.data;
      const newFolder: FilesystemNode = {
        uuid: folderData.uuid,
        name: folderData.name,
        type: "folder",
        parentUuid: folderData.parentUuid,
        fileType: "folder",
        createdAt: folderData.createdAt,
        updatedAt: folderData.updatedAt,
        tags: [],
        fileSize: 0,
        path: folderData.path,
      };

      // Add the new folder directly to the state
      dispatch(addUploadedFiles([newFolder]));

      return response;
    } catch (error) {
      dispatch(
        setCreateFolderError(
          error instanceof Error ? error.message : "Failed to create folder.",
        ),
      );
      throw error;
    } finally {
      dispatch(setCreateFolderLoading(false));
    }
  };

export const listFolderContentsAction =
  (params: ListFolderContentsRequestParams): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setListContentsLoading(true));
      dispatch(setListContentsError(null));
      const response = await listFolderContents(params);

      if (params.cursor) {
        dispatch(appendFolderItems(response.data.items));
      } else {
        dispatch(setFolderItems(response.data.items));
      }

      dispatch(setTotalCount(response.meta.totalCount));
      dispatch(setHasMore(response.meta.hasMore || false));
      dispatch(setNextCursor(response.data.nextCursor || null));
      dispatch(setCurrentFolder(response.data.currentFolder));
      dispatch(setBreadcrumbs(response.data.breadcrumbs));
      dispatch(setLastFetchParams(params));

      return response;
    } catch (error) {
      dispatch(
        setListContentsError(
          error instanceof Error ? error.message : "Failed to list contents.",
        ),
      );
      throw error;
    } finally {
      dispatch(setListContentsLoading(false));
    }
  };

// Updated to NOT use optimistic updates - wait for API call to succeed first
export const deleteNodeAction =
  (uuid: string): AppThunk =>
  async (dispatch: AppDispatch, getState) => {
    try {
      // Set loading state
      dispatch(setDeleteNodeLoading(true));
      dispatch(setDeleteNodeError(null));

      // Call the API first
      await deleteNode(uuid);

      // Only after successful API call, remove from state
      dispatch(removeNodeFromState(uuid));

      // Update the total count after successful deletion
      const state = getState();
      dispatch(setTotalCount(state.fileManagement.totalCount - 1));

      return true;
    } catch (error) {
      dispatch(
        setDeleteNodeError(
          error instanceof Error ? error.message : "Failed to delete item.",
        ),
      );

      throw error;
    } finally {
      dispatch(setDeleteNodeLoading(false));
    }
  };

// Updated to use optimistic updates for rename
export const renameNodeAction =
  (uuid: string, requestData: RenameNodeRequest): AppThunk =>
  async (dispatch: AppDispatch, getState) => {
    // Find the node to rename
    const state = getState();
    const nodeToRename = state.fileManagement.folderItems.find(
      (item) => item.uuid === uuid,
    );

    if (!nodeToRename) return;

    try {
      // Backup the original node
      dispatch(storeRenamedNodeBackup(nodeToRename));

      // Optimistically update the name in state
      dispatch(
        updateNodeNameInState({
          uuid,
          name: requestData.name,
        }),
      );

      // Then call the API in the background
      dispatch(setRenameNodeLoading(true));
      dispatch(setRenameNodeError(null));
      const response = await renameNode(uuid, requestData);
      dispatch(setRenamedNode(response.data));

      return response;
    } catch (error) {
      // If API call fails, restore the original name
      dispatch(restoreRenamedNode());

      dispatch(
        setRenameNodeError(
          error instanceof Error ? error.message : "Failed to rename item.",
        ),
      );

      throw error;
    } finally {
      dispatch(setRenameNodeLoading(false));
    }
  };

// New thunk action for replacing a file
export const replaceFileAction =
  (
    fileUuid: string,
    file: File,
    onUploadProgress?: (progressEvent: AxiosProgressEvent) => void,
  ): AppThunk =>
  async (dispatch: AppDispatch) => {
    try {
      dispatch(setReplaceFileLoading(true));
      dispatch(setReplaceFileError(null));

      const response = await replaceFile(fileUuid, file, onUploadProgress);

      // Store the replaced file data
      dispatch(setReplacedFile(response.data));

      // Update the file in the folder items
      dispatch(updateFileWithReplacedData(response.data));

      return response;
    } catch (error) {
      dispatch(
        setReplaceFileError(
          error instanceof Error ? error.message : "Failed to replace file.",
        ),
      );
      throw error;
    } finally {
      dispatch(setReplaceFileLoading(false));
    }
  };

export default fileManagementSlice.reducer;
