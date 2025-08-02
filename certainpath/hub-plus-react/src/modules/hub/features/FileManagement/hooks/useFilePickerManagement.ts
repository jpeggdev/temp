import { useState, useEffect } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { FilesystemNode } from "../api/listFolderContents/types";
import { AxiosProgressEvent } from "axios";
import {
  listFolderContentsAction,
  setSearchInput,
  setSortBy,
  setSortOrder,
  setFilterType,
  clearFilters,
  createFolderAction,
  deleteNodeAction,
  renameNodeAction,
  toggleSelectedItem as toggleSelectedItemAction,
  clearSelectedItems as clearSelectedItemsAction,
  addUploadedFiles,
  replaceFileAction,
} from "../slices/fileManagementSlice";
import { uploadFilesystemNodes } from "../api/uploadFilesystemNodes/uploadFilesystemNodesApi";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import { downloadAndSaveMultipleNodes } from "../api/downloadMultipleNodes/downloadMultipleNodesApi";
import { useLocation } from "react-router-dom";
import {
  decrementFileTypeCount,
  decrementTagCount,
} from "@/modules/hub/features/FileManagement/slices/fileManagerMetadataSlice";

export interface FileProgress {
  file: File;
  progress: number;
  status: "pending" | "uploading" | "success" | "error";
}

export interface UseFilePickerManagementProps {
  selectedFileTypes?: string[];
  selectedTags?: number[];
}

export function useFilePickerManagement({
  selectedFileTypes = [],
  selectedTags = [],
}: UseFilePickerManagementProps = {}) {
  const location = useLocation();
  const dispatch = useAppDispatch();
  const {
    folderItems,
    totalCount,
    currentFolder,
    breadcrumbs,
    listContentsLoading,
    searchInput,
    sortBy,
    sortOrder,
    filterType,
    selectedItems,
    hasMore,
    nextCursor,
    replaceFileLoading,
  } = useAppSelector((state: RootState) => state.fileManagement);

  const [viewMode, setViewMode] = useState<"grid" | "list">("grid");
  const [isInitialLoading, setIsInitialLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isCreateFolderOpen, setIsCreateFolderOpen] = useState(false);
  const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
  const [isUploadDialogOpen, setIsUploadDialogOpen] = useState(false);
  const [selectedNode, setSelectedNode] = useState<FilesystemNode | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState<FileProgress[]>([]);
  const [isBulkDownloading, setIsBulkDownloading] = useState(false);
  const debouncedSearch = useDebouncedValue(searchInput, 500);

  // Add navigation history tracking
  const [navigationHistory, setNavigationHistory] = useState<{
    stack: (string | null)[];
    currentIndex: number;
  }>({
    stack: [null], // Start with root folder
    currentIndex: 0,
  });

  // Compute whether we can navigate back/forward
  const canNavigateBack = navigationHistory.currentIndex > 0;
  const canNavigateForward =
    navigationHistory.currentIndex < navigationHistory.stack.length - 1;

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const folderUuid = params.get("folder");
    if (folderUuid) {
      loadFolderContents(folderUuid, null, true);
      // Initialize history with the starting folder
      setNavigationHistory({
        stack: [null, folderUuid],
        currentIndex: 1,
      });
    } else {
      loadFolderContents(null, null, true);
      // Initialize history with just the root folder
      setNavigationHistory({
        stack: [null],
        currentIndex: 0,
      });
    }
  }, []);

  useEffect(() => {
    dispatch(clearSelectedItemsAction());
  }, [currentFolder, dispatch]);

  useEffect(() => {
    if (!isInitialLoading) {
      loadFolderContents(currentFolder?.uuid || null, null, true);
    }
  }, [
    debouncedSearch,
    sortBy,
    sortOrder,
    filterType,
    selectedFileTypes,
    selectedTags,
  ]);

  const loadFolderContents = async (
    folderUuid: string | null,
    cursor: string | null = null,
    isRefresh = false,
  ) => {
    try {
      if (isRefresh) {
        setIsRefreshing(true);
      }
      await dispatch(
        listFolderContentsAction({
          folderUuid,
          limit: 20,
          sortBy,
          sortOrder,
          searchTerm: searchInput,
          cursor,
          fileTypes:
            selectedFileTypes.length > 0 ? selectedFileTypes : undefined,
          tags: selectedTags.length > 0 ? selectedTags : undefined,
        }),
      );
      if (isInitialLoading) {
        setIsInitialLoading(false);
      }
    } catch (error) {
      console.error("Error loading folder contents:", error);
      if (isInitialLoading) {
        setIsInitialLoading(false);
      }
    } finally {
      if (isRefresh) {
        setIsRefreshing(false);
      }
    }
  };

  const initializeFileProgress = (files: File[]) => {
    const progressItems: FileProgress[] = files.map((file) => ({
      file,
      progress: 0,
      status: "pending",
    }));
    setUploadProgress(progressItems);
    return progressItems;
  };

  const createProgressCallback = () => {
    return (progressEvent: AxiosProgressEvent) => {
      const percentCompleted = Math.round(
        (progressEvent.loaded * 100) / (progressEvent.total || 100),
      );
      setUploadProgress((prev) =>
        prev.map((item) => ({
          ...item,
          progress: percentCompleted,
          status: percentCompleted < 100 ? "uploading" : "success",
        })),
      );
    };
  };

  const handleFileOperationSuccess = () => {
    setUploadProgress((prev) =>
      prev.map((item) => ({
        ...item,
        progress: 100,
        status: "success",
      })),
    );
    setTimeout(() => {
      setUploadProgress([]);
    }, 2000);
  };

  const handleFileOperationError = () => {
    setUploadProgress((prev) =>
      prev.map((item) => ({
        ...item,
        status: "error",
      })),
    );
    setTimeout(() => {
      setUploadProgress([]);
    }, 5000);
  };

  // Modified folder navigation function
  const handleNavigateToFolder = (
    folderUuid: string | null,
    fromHistory = false,
  ) => {
    // Load folder contents
    loadFolderContents(folderUuid, null, true);

    // Update navigation history (unless this is a history navigation)
    if (!fromHistory) {
      setNavigationHistory((prev) => {
        // If we're not at the end of history, truncate it
        const newStack = prev.stack.slice(0, prev.currentIndex + 1);

        // Don't add if it's the same as current folder
        if (newStack[newStack.length - 1] === folderUuid) {
          return prev;
        }

        // Add new folder to history
        return {
          stack: [...newStack, folderUuid],
          currentIndex: prev.currentIndex + 1,
        };
      });
    }
  };

  // Back navigation
  const handleNavigateBack = () => {
    if (!canNavigateBack) return;

    // Get previous folder and update index
    const newIndex = navigationHistory.currentIndex - 1;
    const previousFolder = navigationHistory.stack[newIndex];

    setNavigationHistory((prev) => ({
      ...prev,
      currentIndex: newIndex,
    }));

    // Navigate to previous folder without updating history
    handleNavigateToFolder(previousFolder, true);
  };

  // Forward navigation
  const handleNavigateForward = () => {
    if (!canNavigateForward) return;

    // Get next folder and update index
    const newIndex = navigationHistory.currentIndex + 1;
    const nextFolder = navigationHistory.stack[newIndex];

    setNavigationHistory((prev) => ({
      ...prev,
      currentIndex: newIndex,
    }));

    // Navigate to next folder without updating history
    handleNavigateToFolder(nextFolder, true);
  };

  const refreshCurrentFolder = () => {
    console.log("inside refresh");
    loadFolderContents(currentFolder?.uuid || null, null, true);
  };

  const fetchMoreItems = () => {
    if (!hasMore || listContentsLoading || !nextCursor) return;
    loadFolderContents(currentFolder?.uuid || null, nextCursor, false);
  };

  const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
    dispatch(setSearchInput(e.target.value));
  };

  const handleSortChange = (
    newSortBy: "name" | "fileType" | "updatedAt" | "fileSize",
  ) => {
    if (sortBy === newSortBy) {
      dispatch(setSortOrder(sortOrder === "ASC" ? "DESC" : "ASC"));
    } else {
      dispatch(setSortBy(newSortBy));
      dispatch(setSortOrder("ASC"));
    }
  };

  const handleFilterChange = (type: string | null) => {
    dispatch(setFilterType(type));
  };

  const handleClearFilters = () => {
    dispatch(clearFilters());
  };

  const handleOpenCreateFolder = () => {
    setIsCreateFolderOpen(true);
  };

  const handleCreateFolder = async (folderName?: string): Promise<void> => {
    await dispatch(
      createFolderAction({
        name: folderName, // This can be undefined now
        parentFolderUuid: currentFolder?.uuid || null,
      }),
    );
  };

  const handleOpenDeleteDialog = (node: FilesystemNode) => {
    setSelectedNode(node);
    setIsDeleteDialogOpen(true);
  };

  const handleDeleteNode = async (): Promise<void> => {
    if (!selectedNode) return;
    try {
      // Get the tags and file type from the selected node before deleting
      const tagsToDecrement = selectedNode.tags || [];
      const fileTypeToDecrement = selectedNode.fileType;
      // Delete the node
      await dispatch(deleteNodeAction(selectedNode.uuid));

      // Update tag counts in the metadata store
      if (tagsToDecrement.length > 0) {
        tagsToDecrement.forEach((tag) => {
          dispatch(decrementTagCount(tag.id));
        });
      }

      // Update file type count in the metadata store if it's a file
      if (fileTypeToDecrement) {
        dispatch(decrementFileTypeCount(fileTypeToDecrement));
      }

      setIsDeleteDialogOpen(false);
    } catch (error) {
      console.error("Error deleting item:", error);
      setIsDeleteDialogOpen(false);
    } finally {
      setSelectedNode(null);
    }
  };

  const handleRenameNode = async (
    nodeUuid: string,
    newName: string,
  ): Promise<void> => {
    try {
      await dispatch(renameNodeAction(nodeUuid, { name: newName }));
    } catch (error) {
      console.error("Error renaming item:", error);
    }
  };

  const handleReplaceFile = async (
    fileUuid: string,
    file: File,
  ): Promise<void> => {
    try {
      initializeFileProgress([file]);
      await dispatch(
        replaceFileAction(fileUuid, file, createProgressCallback()),
      );
      setUploadProgress((prev) =>
        prev.map((item) => ({
          ...item,
          progress: 100,
          status: "success",
        })),
      );
      setTimeout(() => {
        setUploadProgress([]);
      }, 2000);
    } catch {
      handleFileOperationError();
    }
  };

  const handleOpenUploadDialog = () => {
    setIsUploadDialogOpen(true);
  };

  const handleUploadFiles = async (files: File[]): Promise<void> => {
    try {
      setIsUploading(true);
      setIsUploadDialogOpen(false);
      initializeFileProgress(files);
      const response = await uploadFilesystemNodes(
        files,
        currentFolder?.uuid,
        createProgressCallback(),
      );
      const uploadedFiles = response.data.files.map(
        (file) => file as unknown as FilesystemNode,
      );
      dispatch(addUploadedFiles(uploadedFiles));
      handleFileOperationSuccess();
    } catch {
      handleFileOperationError();
    } finally {
      setIsUploading(false);
    }
  };

  const handleBulkDownload = async (): Promise<void> => {
    if (selectedItems.length === 0) return;
    try {
      setIsBulkDownloading(true);
      let defaultFileName = "files.zip";
      if (selectedItems.length === 1) {
        const selectedItem = folderItems.find(
          (item) => item.uuid === selectedItems[0],
        );
        if (selectedItem) {
          defaultFileName = `${selectedItem.name}.zip`;
        }
      }
      await downloadAndSaveMultipleNodes(selectedItems, defaultFileName);
    } catch (error) {
      console.error("Error during bulk download:", error);
    } finally {
      setIsBulkDownloading(false);
    }
  };

  const renderSortIcon = (field: string) => {
    if (sortBy !== field) return null;
    return sortOrder === "ASC" ? " ↑" : " ↓";
  };

  const toggleSelectedItem = (uuid: string) => {
    dispatch(toggleSelectedItemAction(uuid));
  };

  const clearSelectedItems = () => {
    dispatch(clearSelectedItemsAction());
  };

  // Reset hook state
  const resetHookState = () => {
    setViewMode("grid");
    setIsInitialLoading(true);
    setIsRefreshing(false);
    setIsCreateFolderOpen(false);
    setIsDeleteDialogOpen(false);
    setIsUploadDialogOpen(false);
    setSelectedNode(null);
    setIsUploading(false);
    setUploadProgress([]);
    setIsBulkDownloading(false);
    setNavigationHistory({
      stack: [null],
      currentIndex: 0,
    });
  };

  return {
    folderItems,
    totalCount,
    currentFolder,
    breadcrumbs,
    listContentsLoading,
    searchInput,
    sortBy,
    sortOrder,
    filterType,
    hasMore,
    viewMode,
    isInitialLoading,
    isRefreshing,
    isCreateFolderOpen,
    isDeleteDialogOpen,
    isUploadDialogOpen,
    selectedNode,
    isUploading,
    uploadProgress,
    selectedItems,
    isBulkDownloading,
    replaceFileLoading,
    canNavigateBack,
    canNavigateForward,
    setViewMode,
    setIsCreateFolderOpen,
    setIsDeleteDialogOpen,
    setIsUploadDialogOpen,
    setUploadProgress,
    handleNavigateToFolder,
    handleNavigateBack,
    handleNavigateForward,
    fetchMoreItems,
    handleSearch,
    handleSortChange,
    handleFilterChange,
    handleClearFilters,
    handleOpenCreateFolder,
    handleCreateFolder,
    handleOpenDeleteDialog,
    handleDeleteNode,
    handleRenameNode,
    handleReplaceFile,
    handleOpenUploadDialog,
    handleUploadFiles,
    handleBulkDownload,
    renderSortIcon,
    refreshCurrentFolder,
    toggleSelectedItem,
    clearSelectedItems,
    resetHookState,
  };
}
