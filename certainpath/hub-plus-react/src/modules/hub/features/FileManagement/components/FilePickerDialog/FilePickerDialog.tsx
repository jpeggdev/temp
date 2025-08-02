import React, { useRef, useState, useEffect } from "react";
import { Folder, Check, X, Upload, Loader2 } from "lucide-react";
import { Dialog, DialogContent, DialogFooter } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import InfiniteScroll from "react-infinite-scroll-component";
import FileItemPicker from "../FileItemPicker/FileItemPicker";
import "./FilePickerDialog.css";
import { FilesystemNode } from "../../api/listFolderContents/types";
import FilePickerHeader from "../FilePickerHeader/FilePickerHeader";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { fetchFileManagerMetadata } from "../../slices/fileManagerMetadataSlice";
import { mapFileTypesWithIcons } from "../../utils/fileTypeIcons";
import FileDetailsPanel from "../FileDetailsPanel/FileDetailsPanel";
import { resetFileManagementState } from "../../slices/fileManagementSlice";

// Still import these for now, as we're not wiring the actual filtering yet
import { fileTypes, tags } from "../../data/filterData";
import FilePickerFilterSidebar from "@/modules/hub/features/FileManagement/components/FilePickerFilterSidebar/FilePickerFilterSidebar";
import { useFilePickerManagement } from "@/modules/hub/features/FileManagement/hooks/useFilePickerManagement";
import MacStyleUploadIndicator from "@/modules/hub/features/FileManagement/components/MacStyleUploadIndicator/MacStyleUploadIndicator";
import { getPresignedUrls } from "../../api/getPresignedUrls/getPresignedUrlsApi";
import { useNotification } from "@/context/NotificationContext";
import FilePickerLoadingSkeleton from "@/modules/hub/features/FileManagement/components/FilePickerLoadingSkeleton/FilePickerLoadingSkeleton";

interface FilePickerDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onSelect: (
    files: Array<{
      fileUuid: string;
      fileUrl: string;
      presignedUrl: string;
      name: string;
    }>,
  ) => void;
  allowedFileTypes?: string[]; // Optional filter for specific file types
  title?: string;
  multiSelect?: boolean; // New prop for multiple selection
}

export default function FilePickerDialog({
  isOpen,
  onClose,
  onSelect,
  allowedFileTypes,
  multiSelect = false,
}: FilePickerDialogProps) {
  const fileManagerRef = useRef<HTMLDivElement>(null);
  const scrollContainerRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);
  const [isFullScreenSidebar, setIsFullScreenSidebar] = useState(false);
  const [selectedFiles, setSelectedFiles] = useState<FilesystemNode[]>([]);
  const [detailsNode, setDetailsNode] = useState<FilesystemNode | null>(null);
  const [isDetailsOpen, setIsDetailsOpen] = useState(false);
  const dispatch = useAppDispatch();
  const [isMobile, setIsMobile] = useState(false);
  const [isLoadingPresignedUrls, setIsLoadingPresignedUrls] = useState(false);
  const { showNotification } = useNotification();

  // Drag and drop state
  const [isDragging, setIsDragging] = useState(false);
  const dragCounterRef = useRef(0);

  // Track whether sidebar state was changed by user (manual toggle)
  const [sidebarManuallyToggled, setSidebarManuallyToggled] = useState(false);

  // Mac window control states
  const [isFullscreen, setIsFullscreen] = useState(false);

  // Get metadata from Redux store
  const {
    tags: apiTags,
    fileTypes: apiFileTypes,
    loading: metadataLoading,
  } = useAppSelector((state: RootState) => state.fileManagerMetadata);

  // Check if we're on mobile
  useEffect(() => {
    const handleResize = () => {
      const mobile = window.innerWidth < 1024;
      setIsMobile(mobile);

      // Auto-hide sidebar on mobile
      if (mobile && isSidebarOpen && !isFullScreenSidebar) {
        setIsSidebarOpen(false);
        setSidebarManuallyToggled(false); // Reset manual toggle state
      } else if (!mobile && !isSidebarOpen && !sidebarManuallyToggled) {
        // Only auto-show sidebar on desktop if user hasn't manually toggled it
        setIsSidebarOpen(true);
        setIsFullScreenSidebar(false);
      }
    };

    handleResize();
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, [isSidebarOpen, isFullScreenSidebar, sidebarManuallyToggled]);

  // Fetch metadata when component mounts
  useEffect(() => {
    if (isOpen) {
      dispatch(fetchFileManagerMetadata());
    }
  }, [dispatch, isOpen]);

  // Reset state when dialog closes
  useEffect(() => {
    if (!isOpen) {
      // Reset component state
      setSelectedFiles([]);
      setDetailsNode(null);
      setIsDetailsOpen(false);
      setIsFullscreen(false);
      setSidebarManuallyToggled(false);
      setSelectedFileTypes([]);
      setSelectedTags([]);

      // Reset Redux state
      dispatch(resetFileManagementState());
    }
  }, [isOpen, dispatch]);

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      // Reset Redux state when component unmounts
      dispatch(resetFileManagementState());
    };
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
    canNavigateBack,
    canNavigateForward,
    uploadProgress,

    // Setters
    setViewMode,
    setUploadProgress,

    // Handlers
    handleNavigateToFolder,
    handleNavigateBack,
    handleNavigateForward,
    fetchMoreItems,
    handleSearch,
    handleSortChange,
    handleFilterChange,
    handleClearFilters,
    renderSortIcon,
    handleCreateFolder,
    handleRenameNode,
    handleUploadFiles,
  } = useFilePickerManagement({
    selectedFileTypes,
    selectedTags,
  });

  // Get current folder name for the hint
  const currentFolderName =
    currentFolder?.name ||
    (breadcrumbs.length > 0
      ? breadcrumbs[breadcrumbs.length - 1].name
      : "this folder");

  // Drag and drop handlers
  const handleDragEnter = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    dragCounterRef.current++;
    if (e.dataTransfer.items && e.dataTransfer.items.length > 0) {
      setIsDragging(true);
    }
  };

  const handleDragLeave = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    dragCounterRef.current--;
    if (dragCounterRef.current === 0) {
      setIsDragging(false);
    }
  };

  const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    e.dataTransfer.dropEffect = "copy"; // Show the copy icon when dragging
  };

  const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
    dragCounterRef.current = 0;

    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      const files = Array.from(e.dataTransfer.files);
      handleUploadFiles(files);
      e.dataTransfer.clearData();
    }
  };

  // Handle "New Folder" action
  const handleCreateNewFolder = () => {
    // Call the create folder function without a name - backend will generate "Untitled Folder"
    handleCreateFolder();
  };

  // Handle file upload with native file picker
  const handleUploadFilesAction = () => {
    fileInputRef.current?.click();
  };

  // Handle file selection from native file picker
  const handleFileInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    if (files.length > 0) {
      handleUploadFiles(files);
      // Reset the input value so the same file can be selected again
      e.target.value = "";
    }
  };

  const clearAllFilters = () => {
    setSelectedFileTypes([]);
    setSelectedTags([]);
    handleClearFilters();
  };

  const handleItemClick = (item: FilesystemNode) => {
    if (item.type === "file") {
      // Check if this file type is allowed
      const isAllowed = isFileAllowed(item);
      if (!isAllowed) return; // Don't select files that aren't allowed

      if (multiSelect) {
        // For multi-select, toggle the item in the array
        setSelectedFiles((prev) => {
          const isSelected = prev.some((file) => file.uuid === item.uuid);
          if (isSelected) {
            return prev.filter((file) => file.uuid !== item.uuid);
          } else {
            return [...prev, item];
          }
        });
      } else {
        // For single select, replace the selection
        const isCurrentlySelected =
          selectedFiles.length === 1 && selectedFiles[0].uuid === item.uuid;
        setSelectedFiles(isCurrentlySelected ? [] : [item]);
      }
    }
  };

  // Helper function to check if a file is allowed based on its mimeType
  const isFileAllowed = (item: FilesystemNode): boolean => {
    // Folders are always allowed
    if (item.type === "folder") return true;

    // If no file type restrictions, everything is allowed
    if (!allowedFileTypes || allowedFileTypes.length === 0) return true;

    // Check if the file's mime type matches any allowed type
    return allowedFileTypes.some((allowedType) => {
      // If the allowed type ends with *, do a prefix match
      if (allowedType.endsWith("*")) {
        const prefix = allowedType.slice(0, -1);
        return item.mimeType?.startsWith(prefix) || false;
      }
      // Otherwise do an exact match
      return item.mimeType === allowedType;
    });
  };

  const handleConfirmSelection = async () => {
    if (selectedFiles.length === 0) return;

    try {
      setIsLoadingPresignedUrls(true);

      // Get all the file UUIDs
      const fileUuids = selectedFiles.map((file) => file.uuid);

      // Fetch presigned URLs for all selected files
      const response = await getPresignedUrls({
        fileUuids: fileUuids,
      });

      // Prepare the files data with presigned URLs
      const filesData = selectedFiles.map((file) => ({
        fileUuid: file.uuid || "",
        fileUrl: file.url || "",
        presignedUrl: response.data.presignedUrls[file.uuid] || "",
        name: file.name || "",
      }));

      // Call the onSelect callback with the enhanced data
      onSelect(filesData);
      onClose();
    } catch (error) {
      console.error("Error getting presigned URLs:", error);
      showNotification(
        "Error",
        "Failed to retrieve file URLs. Please try again.",
        "error",
      );
    } finally {
      setIsLoadingPresignedUrls(false);
    }
  };

  const toggleSidebar = () => {
    setIsSidebarOpen(!isSidebarOpen);
    setSidebarManuallyToggled(true); // Mark as manually toggled
    setIsFullScreenSidebar(false); // Reset full-screen mode when toggling
  };

  const openSidebar = () => {
    if (isMobile) {
      setIsSidebarOpen(true);
      setIsFullScreenSidebar(true);
    } else {
      setIsSidebarOpen(true);
      setIsFullScreenSidebar(false);
    }
    setSidebarManuallyToggled(true); // Mark as manually toggled
  };

  const closeSidebar = () => {
    setIsSidebarOpen(false);
    setIsFullScreenSidebar(false);
    setSidebarManuallyToggled(true); // Mark as manually toggled
  };

  const handleShowDetails = (item: FilesystemNode) => {
    setDetailsNode(item);
    setIsDetailsOpen(true);
  };

  const closeDetails = () => {
    setIsDetailsOpen(false);
  };

  // Handlers for Mac window controls
  const handleToggleFullscreen = () => {
    setIsFullscreen(!isFullscreen);
  };

  // Apply classes based on Mac window control states
  const dialogClasses = `max-w-6xl p-0 ${isFullscreen ? "fullscreen-dialog" : ""}`;

  // Calculate height for content area based on fullscreen mode
  const contentStyle = isFullscreen
    ? { height: "calc(100vh - 70px)" } // Less space needed in fullscreen for footer
    : { height: "calc(100vh - 140px)" }; // Original height for regular mode

  return (
    <Dialog onOpenChange={onClose} open={isOpen}>
      {/* Added Mac-style controls and classes for fullscreen */}
      <DialogContent className={dialogClasses} hideCloseButton>
        <div className="h-full flex flex-col overflow-hidden sm:rounded-lg">
          {/* Content wrapper with sidebar and main content */}
          <div
            className="flex flex-row relative file-manager-container"
            style={contentStyle}
          >
            {/* Backdrop for full-screen sidebar (mobile only) */}
            {isFullScreenSidebar && (
              <div className="sidebar-backdrop" onClick={closeSidebar} />
            )}

            {/* Filter Sidebar - only shown when isSidebarOpen is true */}
            {isSidebarOpen && (
              <div
                className={`mobile-sidebar-container ${isFullScreenSidebar ? "fullscreen-sidebar" : ""}`}
              >
                <FilePickerFilterSidebar
                  activeFiltersCount={activeFiltersCount}
                  clearAllFilters={clearAllFilters}
                  fileTypes={
                    fileTypesWithIcons.length > 0
                      ? fileTypesWithIcons
                      : fileTypes
                  }
                  filterType={filterType}
                  handleFilterChange={handleFilterChange}
                  handleSearch={handleSearch}
                  isLoading={metadataLoading}
                  isOpen={true} // Always open when rendered
                  onClose={closeSidebar}
                  searchInput={searchInput}
                  selectedFileTypes={selectedFileTypes}
                  selectedTags={selectedTags}
                  tags={apiTags.length > 0 ? apiTags : tags}
                  toggleFileType={toggleFileType}
                  toggleTag={toggleTag}
                />
              </div>
            )}

            {/* Main Content with scrollable area - Always rendered, just hidden by overlay when in full-screen mode */}
            <div
              className={`flex-1 min-w-0 flex flex-col relative ${isDragging ? "finder-drop-target" : ""}`}
              onDragEnter={handleDragEnter}
              onDragLeave={handleDragLeave}
              onDragOver={handleDragOver}
              onDrop={handleDrop}
              ref={fileManagerRef}
            >
              {/* Finder-style drop hint - shown when dragging */}
              {isDragging && (
                <div className="finder-drop-hint">
                  <Upload className="finder-drop-hint-icon" size={24} />
                  <span>Drop to upload to {currentFolderName}</span>
                </div>
              )}

              {/* Header stays outside scrollable area - updated with new props */}
              <FilePickerHeader
                activeFiltersCount={activeFiltersCount}
                breadcrumbs={breadcrumbs}
                canNavigateBack={canNavigateBack}
                canNavigateForward={canNavigateForward}
                handleSearch={handleSearch}
                isFullscreen={isFullscreen}
                isSidebarOpen={isSidebarOpen}
                onClose={onClose}
                onCreateFolder={handleCreateNewFolder}
                onNavigateBack={handleNavigateBack}
                onNavigateBreadcrumb={handleNavigateToFolder}
                onNavigateForward={handleNavigateForward}
                onOpenSidebar={openSidebar}
                onToggleFullscreen={handleToggleFullscreen}
                onToggleSidebar={toggleSidebar}
                onUploadFiles={handleUploadFilesAction}
                searchInput={searchInput}
                setViewMode={setViewMode}
                /*title={title}*/
                viewMode={viewMode}
              />

              {/* Hidden file input for native file picker */}
              <input
                className="hidden"
                multiple
                onChange={handleFileInputChange}
                ref={fileInputRef}
                type="file"
              />

              {/* Scrollable container for infinite scroll */}
              <div
                className="overflow-auto flex-1 p-4"
                id="scrollableContainer"
                ref={scrollContainerRef}
              >
                {/* Sort controls for list view */}
                {viewMode === "list" && (
                  <div className="file-picker-horizontal-scroll">
                    <div className="file-list-header mb-2 border-b border-gray-200 dark:border-gray-700 pb-2 hidden md:flex mt-4 file-list-content">
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
                    </div>
                  </div>
                )}

                {isInitialLoading ? (
                  <FilePickerLoadingSkeleton count={20} />
                ) : (
                  <div
                    className={
                      viewMode === "list" ? "file-picker-horizontal-scroll" : ""
                    }
                  >
                    <InfiniteScroll
                      dataLength={folderItems.length}
                      endMessage={
                        <div className="text-center py-4">
                          <p className="text-gray-500 dark:text-gray-400">
                            {folderItems.length > 0
                              ? "No more items to load"
                              : ""}
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
                      scrollableTarget="scrollableContainer"
                    >
                      {folderItems.length > 0 ? (
                        <div
                          className={
                            viewMode === "grid"
                              ? "grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4"
                              : "flex flex-col gap-1 mt-4 file-list-content"
                          }
                        >
                          {folderItems.map((item) => (
                            <div
                              className={`fade-in ${item.type === "folder" ? "folder-item" : ""}`}
                              key={item.uuid}
                            >
                              <FileItemPicker
                                allowedFileTypes={allowedFileTypes}
                                isSelected={selectedFiles.some(
                                  (file) => file.uuid === item.uuid,
                                )}
                                item={item}
                                onClick={() => handleItemClick(item)}
                                onFolderClick={handleNavigateToFolder}
                                onRename={handleRenameNode}
                                onShowDetails={handleShowDetails}
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
                )}
              </div>
            </div>

            {/* Details Panel - As a third column in the layout */}
            {(!isMobile || (isMobile && isDetailsOpen)) && (
              <FileDetailsPanel
                isMobile={isMobile}
                isOpen={isDetailsOpen}
                node={detailsNode}
                onClose={closeDetails}
              />
            )}
          </div>

          {/* Footer with cancel/select buttons - Always rendered */}
          <DialogFooter className="px-6 py-4 border-t fixed-height-footer">
            <div className="flex items-center justify-between w-full">
              <div>
                {selectedFiles.length > 0 && (
                  <div className="text-sm text-gray-500">
                    {multiSelect
                      ? `Selected: ${selectedFiles.length} file${selectedFiles.length !== 1 ? "s" : ""}`
                      : `Selected: ${selectedFiles[0].name}`}
                  </div>
                )}
              </div>
              <div className="flex gap-2">
                <Button onClick={onClose} variant="outline">
                  <X className="mr-2 h-4 w-4" /> Cancel
                </Button>
                <Button
                  disabled={
                    selectedFiles.length === 0 || isLoadingPresignedUrls
                  }
                  onClick={handleConfirmSelection}
                >
                  {isLoadingPresignedUrls ? (
                    <>
                      <Loader2 className="mr-2 h-4 w-4 animate-spin" />{" "}
                      Loading...
                    </>
                  ) : (
                    <>
                      <Check className="mr-2 h-4 w-4" /> Select
                    </>
                  )}
                </Button>
              </div>
            </div>
          </DialogFooter>

          {/* Upload Progress Indicator */}
          <MacStyleUploadIndicator
            setUploadProgress={setUploadProgress}
            uploadProgress={uploadProgress}
          />
        </div>
      </DialogContent>
    </Dialog>
  );
}
