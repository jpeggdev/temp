import React, { useState } from "react";
import {
  Search,
  Grid,
  List,
  Filter,
  ChevronLeft,
  ChevronRight,
  Folder,
  X,
  SidebarOpen,
  SidebarClose,
  Maximize2,
  FolderPlus,
  ChevronDown,
  Upload,
  CircleEllipsis,
} from "lucide-react";
import { BreadcrumbItem } from "../../api/listFolderContents/types";
import styles from "./FilePickerHeader.module.css";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

interface FilePickerHeaderProps {
  searchInput: string;
  viewMode: "grid" | "list";
  isSidebarOpen: boolean;
  breadcrumbs: BreadcrumbItem[];
  activeFiltersCount: number;
  isFullscreen: boolean;
  canNavigateBack: boolean;
  canNavigateForward: boolean;

  // Handlers
  handleSearch: (e: React.ChangeEvent<HTMLInputElement>) => void;
  setViewMode: (mode: "grid" | "list") => void;
  onOpenSidebar: () => void;
  onToggleSidebar: () => void;
  onNavigateBreadcrumb: (folderUuid: string | null) => void;
  onNavigateBack: () => void;
  onNavigateForward: () => void;
  onClose: () => void;
  onToggleFullscreen: () => void;
  onCreateFolder: () => void; // Handler for folder creation
  onUploadFiles: () => void; // New handler for file uploads
}

const FilePickerHeader: React.FC<FilePickerHeaderProps> = ({
  searchInput,
  viewMode,
  isSidebarOpen,
  breadcrumbs,
  activeFiltersCount,
  isFullscreen,
  canNavigateBack,
  canNavigateForward,
  handleSearch,
  setViewMode,
  onOpenSidebar,
  onToggleSidebar,
  onNavigateBack,
  onNavigateForward,
  onClose,
  onToggleFullscreen,
  onCreateFolder,
  onUploadFiles,
}) => {
  const [isSearchExpanded, setIsSearchExpanded] = useState(false);

  // Get current folder name (last breadcrumb) or "All Files" if at root
  const currentFolder =
    breadcrumbs.length > 0
      ? breadcrumbs[breadcrumbs.length - 1].name
      : "All Files";

  // Toggle search field expansion
  const toggleSearch = () => {
    setIsSearchExpanded(!isSearchExpanded);
  };

  return (
    <div className={styles.container}>
      <div className={styles.toolbar}>
        {/* Mac Window Controls - removed minimize button */}
        <div className={styles.windowControls}>
          <button
            className={`${styles.windowButton} ${styles.closeWindowButton}`}
            onClick={onClose}
            title="Close"
            type="button"
          >
            <X className={styles.buttonIcon} size={8} />
          </button>
          <button
            className={`${styles.windowButton} ${styles.fullscreenWindowButton}`}
            onClick={onToggleFullscreen}
            title={isFullscreen ? "Exit Fullscreen" : "Enter Fullscreen"}
            type="button"
          >
            <Maximize2 className={styles.buttonIcon} size={8} />
          </button>
        </div>

        {/* Navigation buttons - updated to use canNavigateBack/Forward */}
        <div className={styles.navigationButtons}>
          <button
            className={`${styles.navButton} ${!canNavigateBack ? styles.navButtonDisabled : ""}`}
            disabled={!canNavigateBack}
            onClick={onNavigateBack}
          >
            <ChevronLeft size={16} />
          </button>
          <button
            className={`${styles.navButton} ${!canNavigateForward ? styles.navButtonDisabled : ""}`}
            disabled={!canNavigateForward}
            onClick={onNavigateForward}
          >
            <ChevronRight size={16} />
          </button>
        </div>

        {/* Current folder display */}
        <div className={styles.currentFolder}>
          <Folder className={styles.folderIcon} size={16} />
          <span className={styles.folderName}>{currentFolder}</span>
        </div>

        {/* Mac-style Action Button */}
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <button className={styles.macActionMenuButton} title="Actions">
              <div className={styles.macActionButtonContent}>
                <CircleEllipsis
                  className={styles.macActionButtonChevron}
                  size={14}
                />
                <ChevronDown
                  className={styles.macActionButtonChevron}
                  size={12}
                />
              </div>
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="start"
            className={styles.macActionMenu}
            sideOffset={4}
          >
            <DropdownMenuItem
              className={styles.macMenuItem}
              onClick={onCreateFolder}
            >
              <FolderPlus className={styles.macMenuItemIcon} size={14} />
              <span>New Folder</span>
            </DropdownMenuItem>
            <DropdownMenuItem
              className={styles.macMenuItem}
              onClick={onUploadFiles}
            >
              <Upload className={styles.macMenuItemIcon} size={14} />
              <span>Upload Files</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        {/* Spacer to push controls to the right */}
        <div className={styles.spacer}></div>

        {/* Sidebar toggle button (desktop-only) */}
        <button
          className={`${styles.actionButton} ${styles.desktopOnly}`}
          onClick={onToggleSidebar}
          title={isSidebarOpen ? "Hide Sidebar" : "Show Sidebar"}
        >
          {isSidebarOpen ? (
            <SidebarClose size={18} />
          ) : (
            <SidebarOpen size={18} />
          )}
        </button>

        {/* Filter button (mobile only) with badge */}
        <button
          className={`${styles.actionButton} ${styles.mobileOnly} ${styles.filterButton}`}
          onClick={onOpenSidebar}
          title="Filter"
        >
          <Filter size={18} />
          {activeFiltersCount > 0 && (
            <span className={styles.filterBadge}>{activeFiltersCount}</span>
          )}
        </button>

        {/* View toggle */}
        <div className={styles.viewToggle}>
          <button
            className={`${styles.viewButton} ${viewMode === "grid" ? styles.viewButtonActive : ""}`}
            onClick={() => setViewMode("grid")}
            title="Grid view"
          >
            <Grid size={16} />
          </button>
          <button
            className={`${styles.viewButton} ${viewMode === "list" ? styles.viewButtonActive : ""}`}
            onClick={() => setViewMode("list")}
            title="List view"
          >
            <List size={16} />
          </button>
        </div>

        {/* Search area */}
        <div
          className={`${styles.searchArea} ${isSearchExpanded ? styles.searchExpanded : ""}`}
        >
          {isSearchExpanded ? (
            <div className={styles.searchField}>
              <Search className={styles.searchIcon} size={16} />
              <input
                autoFocus
                className={styles.searchInput}
                onChange={handleSearch}
                placeholder="Search"
                type="text"
                value={searchInput}
              />
              <button
                className={styles.closeSearchButton}
                onClick={toggleSearch}
              >
                <X size={16} />
              </button>
            </div>
          ) : (
            <button
              className={styles.searchButton}
              onClick={toggleSearch}
              title="Search"
            >
              <Search size={18} />
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default FilePickerHeader;
