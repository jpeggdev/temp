import React, { useState, useRef, useEffect } from "react";
import {
  Search,
  Grid,
  List,
  Plus,
  Upload,
  X,
  CheckSquare,
  Trash2,
  Download,
  Loader2,
  Filter,
  MoreVertical,
  ChevronRight,
  Home,
  Folder,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { FilesystemNode } from "../../api/listFolderContents/types";
import { BreadcrumbItem } from "../../api/listFolderContents/types";
import styles from "./FileManagementHeader.module.css";

interface FileManagementHeaderProps {
  searchInput: string;
  viewMode: "grid" | "list";
  isSelectionMode: boolean;
  selectedItems: string[];
  folderItems: FilesystemNode[];
  isBulkDeleting: boolean;
  isBulkDownloading: boolean;
  isSidebarOpen: boolean;
  breadcrumbs: BreadcrumbItem[];

  // Handlers
  handleSearch: (e: React.ChangeEvent<HTMLInputElement>) => void;
  setViewMode: (mode: "grid" | "list") => void;
  handleOpenUploadDialog: () => void;
  handleOpenCreateFolder: () => void;
  toggleSelectionMode: () => void;
  handleSelectAll: () => void;
  handleBulkDownload: () => void;
  handleBulkDelete: () => void;
  onOpenSidebar: () => void;
  onNavigateBreadcrumb: (folderUuid: string | null) => void;
}

const FileManagementHeader: React.FC<FileManagementHeaderProps> = ({
  searchInput,
  viewMode,
  isSelectionMode,
  selectedItems,
  folderItems,
  isBulkDeleting,
  isBulkDownloading,
  breadcrumbs,

  // Handlers
  handleSearch,
  setViewMode,
  handleOpenUploadDialog,
  handleOpenCreateFolder,
  toggleSelectionMode,
  handleSelectAll,
  handleBulkDownload,
  handleBulkDelete,
  onOpenSidebar,
  onNavigateBreadcrumb,
}) => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isSticky, setIsSticky] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);
  const headerRef = useRef<HTMLDivElement>(null);
  const headerPositionRef = useRef<number | null>(null);

  // Close menu when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        setIsMenuOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  // Store the header's initial position on mount
  useEffect(() => {
    const calculateHeaderPosition = () => {
      if (headerRef.current) {
        // Get the header's position relative to the document
        const rect = headerRef.current.getBoundingClientRect();
        const scrollTop =
          window.pageYOffset || document.documentElement.scrollTop;
        const headerTop = rect.top + scrollTop;
        headerPositionRef.current = headerTop;
      }
    };

    // Calculate on mount
    calculateHeaderPosition();

    // Recalculate on resize
    window.addEventListener("resize", calculateHeaderPosition);
    return () => {
      window.removeEventListener("resize", calculateHeaderPosition);
    };
  }, []);

  // Add scroll event listener to detect when header should be sticky
  useEffect(() => {
    const stickyThreshold = 64; // The top offset (matches the CSS sticky position)

    const handleScroll = () => {
      if (headerPositionRef.current === null || !headerRef.current) return;

      const headerRect = headerRef.current.getBoundingClientRect();

      // Apply sticky class when the header's top edge is at or above the sticky threshold
      if (headerRect.top <= stickyThreshold) {
        if (!isSticky) setIsSticky(true);
      } else {
        if (isSticky) setIsSticky(false);
      }
    };

    window.addEventListener("scroll", handleScroll);

    // Run once to check initial position
    handleScroll();

    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, [isSticky]);

  // Helper to close menu after selecting an option
  const handleMenuItemClick = (callback: () => void) => {
    return () => {
      callback();
      setIsMenuOpen(false);
    };
  };

  return (
    <div
      className={`${styles.container} ${isSticky ? styles.sticky : ""}`}
      ref={headerRef}
    >
      {/* Compact breadcrumbs that only appear when sticky */}
      {isSticky && (
        <div className={styles.compactBreadcrumbs}>
          <button
            className={styles.compactBreadcrumbHome}
            onClick={() => onNavigateBreadcrumb(null)}
          >
            <Home size={14} />
          </button>

          {breadcrumbs.length > 0 && (
            <ChevronRight className={styles.compactSeparator} size={14} />
          )}

          {breadcrumbs.map((crumb, index) => {
            // If this is the last breadcrumb, show it
            const isLast = index === breadcrumbs.length - 1;

            // For other breadcrumbs, only show if there are 3 or fewer total, or it's one of the first/last
            const shouldShow = isLast || breadcrumbs.length <= 3 || index === 0;

            return shouldShow ? (
              <React.Fragment key={crumb.uuid}>
                <button
                  className={`${styles.compactBreadcrumbItem} ${isLast ? styles.compactCurrentItem : ""}`}
                  onClick={() => onNavigateBreadcrumb(crumb.uuid)}
                  title={crumb.name}
                >
                  {isLast && (
                    <Folder className={styles.compactFolderIcon} size={12} />
                  )}
                  <span className={styles.compactBreadcrumbText}>
                    {crumb.name}
                  </span>
                </button>
                {index < breadcrumbs.length - 1 && (
                  <ChevronRight className={styles.compactSeparator} size={14} />
                )}
              </React.Fragment>
            ) : index === 1 && breadcrumbs.length > 3 ? (
              <span
                className={styles.compactEllipsis}
                key={`ellipsis-${index}`}
              >
                ...
              </span>
            ) : null;
          })}
        </div>
      )}

      <div className={styles.headerContent}>
        {/* Search row - now on its own line */}
        <div className={styles.searchRow}>
          <div className={styles.searchWrapper}>
            <div className={styles.searchContainer}>
              <Search className={styles.searchIcon} size={20} />
              <input
                className={styles.searchInput}
                onChange={handleSearch}
                placeholder="Search files and folders"
                type="text"
                value={searchInput}
              />
            </div>
          </div>
        </div>

        {/* Actions row */}
        <div className={styles.actionsRow}>
          {/* Mobile filter button - UPDATED from md:hidden to lg:hidden */}
          <Button
            className="lg:hidden mr-2"
            onClick={onOpenSidebar}
            size="icon"
            variant="outline"
          >
            <Filter size={18} />
          </Button>

          {!isSelectionMode ? (
            /* Standard mode actions */
            <div className={styles.standardActions}>
              {/* Primary actions - shown on all screen sizes */}
              <Button
                className={`${styles.uploadButton} ${styles.primaryAction}`}
                onClick={handleOpenUploadDialog}
              >
                <Upload className={styles.buttonIcon} size={16} />
                <span className={styles.buttonText}>Upload</span>
              </Button>

              {/* Secondary actions - hidden on smaller screens */}
              <Button
                className={`${styles.folderButton} ${styles.secondaryAction}`}
                onClick={handleOpenCreateFolder}
              >
                <Plus className={styles.buttonIcon} size={16} />
                <span className={styles.buttonText}>New Folder</span>
              </Button>

              <Button
                className={`${styles.selectButton} ${styles.secondaryAction}`}
                onClick={toggleSelectionMode}
                variant="outline"
              >
                <CheckSquare className={styles.buttonIcon} size={16} />
                <span className={styles.buttonText}>Select</span>
              </Button>
            </div>
          ) : (
            /* Selection mode actions */
            <div className={styles.selectionActions}>
              <span className={styles.selectionCount}>
                {selectedItems.length} selected
              </span>

              {/* Primary actions in selection mode */}
              <Button
                className={`${styles.deleteButton} ${styles.primaryAction}`}
                disabled={selectedItems.length === 0 || isBulkDeleting}
                onClick={handleBulkDelete}
                variant="destructive"
              >
                {isBulkDeleting ? (
                  <Loader2
                    className={`${styles.buttonIcon} ${styles.spinningIcon}`}
                    size={16}
                  />
                ) : (
                  <Trash2 className={styles.buttonIcon} size={16} />
                )}
                <span className={styles.buttonText}>Delete</span>
              </Button>

              {/* Secondary actions in selection mode */}
              <Button
                className={`${styles.downloadButton} ${styles.secondaryAction}`}
                disabled={selectedItems.length === 0 || isBulkDownloading}
                onClick={handleBulkDownload}
                variant="outline"
              >
                {isBulkDownloading ? (
                  <Loader2
                    className={`${styles.buttonIcon} ${styles.spinningIcon}`}
                    size={16}
                  />
                ) : (
                  <Download className={styles.buttonIcon} size={16} />
                )}
                <span className={styles.buttonText}>Download</span>
              </Button>

              <Button
                className={`${styles.selectAllButton} ${styles.secondaryAction}`}
                onClick={handleSelectAll}
                variant="outline"
              >
                <CheckSquare className={styles.buttonIcon} size={16} />
                <span className={styles.buttonText}>
                  {selectedItems.length === folderItems.length
                    ? "Deselect All"
                    : "Select All"}
                </span>
              </Button>

              <Button
                className={`${styles.cancelButton} ${styles.secondaryAction}`}
                onClick={toggleSelectionMode}
                variant="outline"
              >
                <X className={styles.buttonIcon} size={16} />
                <span className={styles.buttonText}>Cancel</span>
              </Button>
            </div>
          )}

          {/* More options dropdown for smaller screens */}
          <div className={styles.moreOptionsWrapper} ref={menuRef}>
            <Button
              className={styles.moreOptionsButton}
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              size="icon"
              variant="outline"
            >
              <MoreVertical size={18} />
            </Button>

            {isMenuOpen && (
              <div className={styles.dropdownMenu}>
                {!isSelectionMode ? (
                  /* Standard mode options */
                  <>
                    <button
                      className={styles.dropdownMenuItem}
                      onClick={handleMenuItemClick(handleOpenUploadDialog)}
                    >
                      <Upload className={styles.menuItemIcon} size={16} />
                      Upload
                    </button>
                    <button
                      className={styles.dropdownMenuItem}
                      onClick={handleMenuItemClick(handleOpenCreateFolder)}
                    >
                      <Plus className={styles.menuItemIcon} size={16} />
                      New Folder
                    </button>
                    <button
                      className={styles.dropdownMenuItem}
                      onClick={handleMenuItemClick(toggleSelectionMode)}
                    >
                      <CheckSquare className={styles.menuItemIcon} size={16} />
                      Select
                    </button>

                    <div className={styles.dropdownDivider} />

                    <button
                      className={`${styles.dropdownMenuItem} ${viewMode === "grid" ? styles.activeMenuItem : ""}`}
                      onClick={handleMenuItemClick(() => setViewMode("grid"))}
                    >
                      <Grid className={styles.menuItemIcon} size={16} />
                      Grid View
                    </button>
                    <button
                      className={`${styles.dropdownMenuItem} ${viewMode === "list" ? styles.activeMenuItem : ""}`}
                      onClick={handleMenuItemClick(() => setViewMode("list"))}
                    >
                      <List className={styles.menuItemIcon} size={16} />
                      List View
                    </button>
                  </>
                ) : (
                  /* Selection mode options */
                  <>
                    <button
                      className={styles.dropdownMenuItem}
                      disabled={selectedItems.length === 0 || isBulkDeleting}
                      onClick={handleMenuItemClick(handleBulkDelete)}
                    >
                      <Trash2 className={styles.menuItemIcon} size={16} />
                      Delete
                    </button>
                    <button
                      className={styles.dropdownMenuItem}
                      disabled={selectedItems.length === 0 || isBulkDownloading}
                      onClick={handleMenuItemClick(handleBulkDownload)}
                    >
                      <Download className={styles.menuItemIcon} size={16} />
                      Download
                    </button>
                    <button
                      className={styles.dropdownMenuItem}
                      onClick={handleMenuItemClick(handleSelectAll)}
                    >
                      <CheckSquare className={styles.menuItemIcon} size={16} />
                      {selectedItems.length === folderItems.length
                        ? "Deselect All"
                        : "Select All"}
                    </button>
                    <button
                      className={styles.dropdownMenuItem}
                      onClick={handleMenuItemClick(toggleSelectionMode)}
                    >
                      <X className={styles.menuItemIcon} size={16} />
                      Cancel
                    </button>
                  </>
                )}
              </div>
            )}
          </div>

          {/* View toggle - always visible on desktop */}
          <div className={styles.viewToggle}>
            <button
              className={`${styles.viewToggleButton} ${
                viewMode === "grid" ? styles.viewToggleButtonActive : ""
              }`}
              onClick={() => setViewMode("grid")}
              title="Grid view"
            >
              <Grid size={18} />
            </button>
            <button
              className={`${styles.viewToggleButton} ${
                viewMode === "list" ? styles.viewToggleButtonActive : ""
              }`}
              onClick={() => setViewMode("list")}
              title="List view"
            >
              <List size={18} />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default FileManagementHeader;
