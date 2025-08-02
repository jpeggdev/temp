import React, { useRef, useState, useEffect } from "react";
import { Folder } from "lucide-react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import InfiniteScroll from "react-infinite-scroll-component";
import FileItem from "../FileItem/FileItem";
import FileManagerLoadingSkeleton from "../FileManagerLoadingSkeleton/FileManagerLoadingSkeleton";
import Breadcrumbs from "../Breadcrumbs/Breadcrumbs";
import CreateFolderDialog from "../CreateFolderDialog/CreateFolderDialog";
import DeleteFileDialog from "../DeleteFileDialog/DeleteFileDialog";
import SimpleFileUpload from "../SimpleFileUpload/SimpleFileUpload";
import BulkDeleteDialog from "../BulkDeleteDialog/BulkDeleteDialog";
import { useFileManagement } from "../../hooks/useFileManagement";
import { useBulkDeleteFiles } from "../../hooks/useBulkDeleteFiles";
import "./FileManagement.css";
import styles from "./FileManagement.module.css";
import { DraggableFileWrapper } from "../../components/DraggableFileWrapper/DraggableFileWrapper";
import { FilesystemNode } from "../../api/listFolderContents/types";
import FileManagementHeader from "../FileManagementHeader/FileManagementHeader";
import UploadProgressIndicator from "../UploadProgressIndicator/UploadProgressIndicator";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import FilterSidebar from "@/modules/hub/features/FileManagement/components/FilterSidebar/FilterSidebar";
import { fetchFileManagerMetadata } from "../../slices/fileManagerMetadataSlice";
import { mapFileTypesWithIcons } from "../../utils/fileTypeIcons";

// Still import these for now, as we're not wiring the actual filtering yet
import { fileTypes, tags } from "../../data/filterData";

export default function FileManagement() {
  const [isSelectionMode, setIsSelectionMode] = useState(false);
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const dispatch = useAppDispatch();

  // Get metadata from Redux store
  const {
    tags: apiTags,
    fileTypes: apiFileTypes,
    loading: metadataLoading,
  } = useAppSelector((state: RootState) => state.fileManagerMetadata);

  // Fetch metadata when component mounts
  useEffect(() => {
    dispatch(fetchFileManagerMetadata());
  }, [dispatch]);

  // Map API file types to the format expected by the sidebar
  const fileTypesWithIcons = mapFileTypesWithIcons(apiFileTypes);

  // Filter state
  const [selectedFileTypes, setSelectedFileTypes] = useState<string[]>([]);
  const [selectedTags, setSelectedTags] = useState<number[]>([]);

  // Helper functions for filter UI
  const toggleFileType = (typeId: string) => {
    setSelectedFileTypes((prev) =>
      prev.includes(typeId)
        ? prev.filter((id) => id !== typeId)
        : [...prev, typeId],
    );
  };

  const toggleTag = (tagId: number) => {
    setSelectedTags((prev) =>
      prev.includes(tagId)
        ? prev.filter((id) => id !== tagId)
        : [...prev, tagId],
    );
  };

  // Count active filters for badge
  const activeFiltersCount = selectedFileTypes.length + selectedTags.length;

  const {
    // State
    folderItems,
    currentFolder,
    breadcrumbs,
    searchInput,
    filterType,
    hasMore,
    viewMode,
    isInitialLoading,
    isCreateFolderOpen,
    isDeleteDialogOpen,
    isUploadDialogOpen,
    selectedNode,
    uploadProgress,
    selectedItems,
    isBulkDownloading,

    // Setters
    setViewMode,
    setIsCreateFolderOpen,
    setIsDeleteDialogOpen,
    setIsUploadDialogOpen,
    setUploadProgress,

    // Handlers
    handleNavigateToFolder,
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
    toggleSelectedItem,
    clearSelectedItems,
    refreshCurrentFolder,
  } = useFileManagement({
    selectedFileTypes,
    selectedTags,
  });

  // Bulk delete functionality using the new hook
  const {
    isBulkDeleteDialogOpen,
    deleteJob,
    isBulkDeleting,
    handleOpenBulkDeleteDialog,
    handleCloseBulkDeleteDialog,
    handleBulkDelete,
  } = useBulkDeleteFiles({
    refreshFolder: refreshCurrentFolder,
  });

  const toggleSelectionMode = () => {
    if (isSelectionMode) {
      // Exit selection mode and clear selections
      clearSelectedItems();
    }
    setIsSelectionMode(!isSelectionMode);
  };

  const handleSelectAll = () => {
    // If all items are already selected, deselect all
    if (selectedItems.length === folderItems.length) {
      clearSelectedItems();
    } else {
      // Otherwise select all items
      folderItems.forEach((item) => {
        if (!selectedItems.includes(item.uuid)) {
          toggleSelectedItem(item.uuid);
        }
      });
    }
  };

  const handleItemClick = (item: FilesystemNode) => {
    if (isSelectionMode) {
      toggleSelectedItem(item.uuid);
    } else if (item.type === "folder") {
      handleNavigateToFolder(item.uuid);
    }
  };

  const clearAllFilters = () => {
    setSelectedFileTypes([]);
    setSelectedTags([]);
    handleClearFilters();
  };

  // Handle initiating the bulk delete process
  const handleStartBulkDelete = () => {
    if (selectedItems.length > 0) {
      handleOpenBulkDeleteDialog();
    }
  };

  // Execute the bulk delete when confirmed in the dialog
  const executeBulkDelete = () => {
    return handleBulkDelete(selectedItems);
  };

  if (isInitialLoading) {
    return (
      <MainPageWrapper>
        <FileManagerLoadingSkeleton count={20} />
      </MainPageWrapper>
    );
  }

  return (
    <MainPageWrapper>
      {/* Added gradient header */}
      <div className={styles.headerContainer}>
        <div className={styles.pageHeaderContent}>
          <h1 className={styles.pageTitle}>File Management</h1>
          <p className={styles.pageSubtitle}>
            Manage and organize your files and folders
          </p>
        </div>
      </div>

      <div className="flex flex-col md:flex-row gap-6">
        {/* Filter Sidebar - now uses API data */}
        <FilterSidebar
          activeFiltersCount={activeFiltersCount}
          clearAllFilters={clearAllFilters}
          fileTypes={
            fileTypesWithIcons.length > 0 ? fileTypesWithIcons : fileTypes
          }
          filterType={filterType}
          handleFilterChange={handleFilterChange}
          handleSearch={handleSearch}
          isLoading={metadataLoading}
          isOpen={isSidebarOpen}
          onClose={() => setIsSidebarOpen(false)}
          searchInput={searchInput}
          selectedFileTypes={selectedFileTypes}
          selectedTags={selectedTags}
          tags={apiTags.length > 0 ? apiTags : tags}
          toggleFileType={toggleFileType}
          toggleTag={toggleTag}
        />

        {/* Main Content */}
        <div className="flex-1 min-w-0">
          <DraggableFileWrapper onFilesDropped={handleUploadFiles}>
            <FileManagementHeader
              breadcrumbs={breadcrumbs}
              folderItems={folderItems}
              handleBulkDelete={handleStartBulkDelete}
              handleBulkDownload={handleBulkDownload}
              handleOpenCreateFolder={handleOpenCreateFolder}
              handleOpenUploadDialog={handleOpenUploadDialog}
              handleSearch={handleSearch}
              handleSelectAll={handleSelectAll}
              isBulkDeleting={isBulkDeleting}
              isBulkDownloading={isBulkDownloading}
              isSelectionMode={isSelectionMode}
              isSidebarOpen={isSidebarOpen}
              onNavigateBreadcrumb={handleNavigateToFolder}
              onOpenSidebar={() => setIsSidebarOpen(true)}
              searchInput={searchInput}
              selectedItems={selectedItems}
              setViewMode={setViewMode}
              toggleSelectionMode={toggleSelectionMode}
              viewMode={viewMode}
            />

            <div className="pt-4">
              {/* Breadcrumbs navigation */}
              <Breadcrumbs
                breadcrumbs={breadcrumbs}
                onNavigate={handleNavigateToFolder}
              />

              {/* Sort controls for list view */}
              {viewMode === "list" && (
                <div className="file-list-header mb-2 border-b border-gray-200 dark:border-gray-700 pb-2 hidden md:flex mt-4">
                  {isSelectionMode && (
                    <div className="w-8 flex items-center"></div>
                  )}
                  <div className="flex-1 flex items-center gap-2">
                    <button
                      className="text-sm font-medium flex items-center gap-1"
                      onClick={() => handleSortChange("name")}
                    >
                      Name{renderSortIcon("name")}
                    </button>
                  </div>
                  <div className="w-24 text-sm font-medium flex items-center gap-1">
                    <button
                      className="text-sm font-medium flex items-center gap-1"
                      onClick={() => handleSortChange("fileType")}
                    >
                      Type{renderSortIcon("fileType")}
                    </button>
                  </div>
                  <div className="w-32 text-sm font-medium flex items-center gap-1">
                    <button
                      className="text-sm font-medium flex items-center gap-1"
                      onClick={() => handleSortChange("updatedAt")}
                    >
                      Modified{renderSortIcon("updatedAt")}
                    </button>
                  </div>
                  <div className="w-24 text-sm font-medium">Size</div>
                  <div className="w-8"></div>
                </div>
              )}

              <div className="pb-20">
                <InfiniteScroll
                  dataLength={folderItems.length}
                  endMessage={
                    <div className="text-center py-4">
                      <p className="text-gray-500 dark:text-gray-400">
                        {folderItems.length > 0 ? "No more items to load" : ""}
                      </p>
                    </div>
                  }
                  hasMore={hasMore}
                  loader={
                    <div className="text-center py-4">
                      <p className="text-gray-500 dark:text-gray-400">
                        Loading more...
                      </p>
                    </div>
                  }
                  next={fetchMoreItems}
                  style={{ overflow: "visible" }}
                >
                  {folderItems.length > 0 ? (
                    <div
                      className={
                        viewMode === "grid"
                          ? "grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4"
                          : "flex flex-col gap-1 mt-4"
                      }
                    >
                      {folderItems.map((item) => (
                        <div className="fade-in" key={item.uuid}>
                          <FileItem
                            isSelected={selectedItems.includes(item.uuid)}
                            isSelectionMode={isSelectionMode}
                            item={item}
                            onClick={() => handleItemClick(item)}
                            onDelete={() => handleOpenDeleteDialog(item)}
                            onFolderClick={() =>
                              handleNavigateToFolder(item.uuid)
                            }
                            onRename={(newName) =>
                              handleRenameNode(item.uuid, newName)
                            }
                            onReplaceFile={(fileUuid: string, file: File) =>
                              handleReplaceFile(fileUuid, file)
                            }
                            onToggleSelect={() => toggleSelectedItem(item.uuid)}
                            viewMode={viewMode}
                          />
                        </div>
                      ))}
                    </div>
                  ) : (
                    <div className="text-center py-12">
                      <Folder
                        className="mx-auto mb-4 text-gray-400"
                        size={48}
                      />
                      <p className="text-gray-500 dark:text-gray-400 text-lg">
                        {searchInput || activeFiltersCount > 0
                          ? "No items match your filters."
                          : "This folder is empty."}
                      </p>
                      {(searchInput || activeFiltersCount > 0) && (
                        <button
                          className="mt-4 text-blue-600 dark:text-blue-400 hover:underline"
                          onClick={clearAllFilters}
                        >
                          Clear all filters
                        </button>
                      )}
                    </div>
                  )}
                </InfiniteScroll>
              </div>
            </div>

            {/* Upload progress indicator */}
            <UploadProgressIndicator
              setUploadProgress={setUploadProgress}
              uploadProgress={uploadProgress}
            />

            {/* Modals */}
            <CreateFolderDialog
              currentFolder={currentFolder}
              isOpen={isCreateFolderOpen}
              onClose={() => setIsCreateFolderOpen(false)}
              onConfirm={handleCreateFolder}
            />

            <DeleteFileDialog
              file={selectedNode}
              isOpen={isDeleteDialogOpen}
              onClose={() => setIsDeleteDialogOpen(false)}
              onConfirm={handleDeleteNode}
            />

            <BulkDeleteDialog
              deleteJob={deleteJob}
              isOpen={isBulkDeleteDialogOpen}
              itemCount={selectedItems.length}
              onClose={handleCloseBulkDeleteDialog}
              onConfirm={executeBulkDelete}
            />

            <SimpleFileUpload
              currentFolderId={currentFolder?.uuid}
              isOpen={isUploadDialogOpen}
              maxFileSize={100 * 1024 * 1024} // 100MB
              onClose={() => setIsUploadDialogOpen(false)}
              onUpload={handleUploadFiles}
            />
          </DraggableFileWrapper>
        </div>
      </div>
    </MainPageWrapper>
  );
}
