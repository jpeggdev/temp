import React, { useState, useRef, useEffect } from "react";
import {
  File,
  Folder,
  FileText,
  Image,
  Video,
  Music,
  Package,
  Download,
  Trash2,
  Edit2,
  Info,
  Loader2,
  Tag,
  RefreshCw,
  CheckSquare,
  Square,
  Clock,
  FileType,
} from "lucide-react";
import { FilesystemNode } from "../../api/listFolderContents/types";
import {
  ContextMenu,
  ContextMenuContent,
  ContextMenuItem,
  ContextMenuTrigger,
  ContextMenuSeparator,
} from "@/components/ui/context-menu";
import { Checkbox } from "@/components/ui/checkbox";
import { formatFileSize } from "@/modules/hub/features/FileManagement/utils/formatters";
import { formatDate } from "@/utils/dateUtils";
import { downloadAndSaveFilesystemNode } from "../../api/downloadFilesystemNode/downloadFilesystemNodeApi";
import { downloadAndSaveMultipleNodes } from "../../api/downloadMultipleNodes/downloadMultipleNodesApi";
import { toast } from "@/components/ui/use-toast";
import FileDetailsDrawer from "../FileDetailsDrawer/FileDetailsDrawer";
import TagPopover from "../TagPopover/TagPopover";
import ReplaceFileDialog from "../ReplaceFileDialog/ReplaceFileDialog";
import { Input } from "@/components/ui/input";
import styles from "./FileItem.module.css";

interface FileItemProps {
  item: FilesystemNode;
  viewMode: "grid" | "list";
  onFolderClick: (uuid: string) => void;
  onDelete: () => void;
  onRename: (newName: string) => Promise<void>;
  onReplaceFile?: (fileUuid: string, file: File) => Promise<void>;
  isSelectionMode?: boolean;
  isSelected?: boolean;
  onToggleSelect?: () => void;
  onClick?: () => void;
}

const FileItem: React.FC<FileItemProps> = ({
  item,
  viewMode,
  onFolderClick,
  onDelete,
  onRename,
  onReplaceFile,
  isSelectionMode = false,
  isSelected = false,
  onToggleSelect,
  onClick,
}) => {
  const [isDownloading, setIsDownloading] = useState<boolean>(false);
  const [isDetailsDrawerOpen, setIsDetailsDrawerOpen] =
    useState<boolean>(false);
  const [isTagPopoverOpen, setIsTagPopoverOpen] = useState<boolean>(false);
  const [isReplaceDialogOpen, setIsReplaceDialogOpen] =
    useState<boolean>(false);
  const [isRenaming, setIsRenaming] = useState<boolean>(false);
  const [newName, setNewName] = useState<string>(item.name);
  const [isRenamingLoading, setIsRenamingLoading] = useState<boolean>(false);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    // Focus the input when renaming starts
    if (isRenaming && inputRef.current) {
      setTimeout(() => {
        inputRef.current?.focus();
      }, 50);
      // Select the filename without extension
      const lastDotIndex =
        item.type === "file" ? item.name.lastIndexOf(".") : -1;
      if (lastDotIndex > 0) {
        inputRef.current.setSelectionRange(0, lastDotIndex);
      } else {
        inputRef.current.select();
      }
    }
  }, [isRenaming, item]);

  const getFileIcon = () => {
    // For folders, always use the folder icon
    if (item.type === "folder") {
      return (
        <Folder
          className={styles.folderIcon}
          size={viewMode === "grid" ? 28 : 20}
        />
      );
    }

    // Use the fileType from the backend
    switch (item.fileType) {
      case "image":
        return (
          <Image
            className={styles.imageIcon}
            size={viewMode === "grid" ? 28 : 20}
          />
        );
      case "pdf":
        return (
          <FileText
            className={styles.pdfIcon}
            size={viewMode === "grid" ? 28 : 20}
          />
        );
      case "document":
        return (
          <FileText
            className={styles.documentIcon}
            size={viewMode === "grid" ? 28 : 20}
          />
        );
      case "spreadsheet":
        return (
          <FileText
            className={styles.spreadsheetIcon}
            size={viewMode === "grid" ? 28 : 20}
          />
        );
      case "presentation":
        return (
          <FileText
            className={styles.presentationIcon}
            size={viewMode === "grid" ? 28 : 20}
          />
        );
      case "video":
        return (
          <Video
            className={styles.videoIcon}
            size={viewMode === "grid" ? 28 : 20}
          />
        );
      case "audio":
        return (
          <Music
            className={styles.audioIcon}
            size={viewMode === "grid" ? 28 : 20}
          />
        );
      case "archive":
        return (
          <Package
            className={styles.archiveIcon}
            size={viewMode === "grid" ? 28 : 20}
          />
        );
      default:
        return (
          <File
            className={styles.genericFileIcon}
            size={viewMode === "grid" ? 28 : 20}
          />
        );
    }
  };

  const handleClick = () => {
    if (isRenaming) return; // Prevent actions when renaming

    if (isSelectionMode) {
      // When in selection mode, clicking anywhere on the item should toggle selection
      onToggleSelect?.();
    } else if (onClick) {
      onClick();
    } else if (item.type === "folder") {
      onFolderClick(item.uuid);
    } else {
      // For files, open the details drawer
      setIsDetailsDrawerOpen(true);
    }
  };

  const handleStartRename = (e?: React.MouseEvent) => {
    if (e) {
      e.stopPropagation(); // Prevent item click handler
    }
    setNewName(item.name);
    setIsRenaming(true);
  };

  const handleCancelRename = (e?: React.MouseEvent) => {
    if (e) {
      e.stopPropagation(); // Prevent item click handler
    }
    setIsRenaming(false);
    setNewName(item.name);
  };

  const handleSaveRename = async (e?: React.MouseEvent) => {
    if (e) {
      e.stopPropagation(); // Prevent item click handler
    }

    if (newName.trim() === "" || newName === item.name) {
      handleCancelRename();
      return;
    }

    setIsRenamingLoading(true);
    try {
      await onRename(newName);
      setIsRenaming(false);
      toast({
        title: "Renamed successfully",
        description: `"${item.name}" has been renamed to "${newName}"`,
      });
    } catch {
      toast({
        title: "Error renaming",
        description: "Failed to rename the item. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsRenamingLoading(false);
    }
  };

  const handleInputKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter") {
      handleSaveRename();
    } else if (e.key === "Escape") {
      handleCancelRename();
    }
  };

  const handleInputBlur = () => {
    // Save on blur, but only if the name has actually changed
    if (newName.trim() !== "" && newName !== item.name) {
      handleSaveRename();
    } else {
      handleCancelRename();
    }
  };

  const handleDelete = () => {
    onDelete();
  };

  const handleDownload = async () => {
    if (!item.uuid) {
      return;
    }

    setIsDownloading(true);

    try {
      if (item.type === "folder") {
        // For folders, download as a zip using the multiple nodes API
        await downloadAndSaveMultipleNodes([item.uuid], `${item.name}.zip`);
      } else {
        // For files, use the single file download API
        await downloadAndSaveFilesystemNode(item.uuid, item.name);
      }

      toast({
        title: "Download successful",
        description: `${item.name} has been downloaded.`,
      });
    } catch (error) {
      console.error("Error downloading:", error);
      toast({
        title: "Download failed",
        description: "There was a problem downloading. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsDownloading(false);
    }
  };

  const handleShowDetails = () => {
    setIsDetailsDrawerOpen(true);
  };

  const handleOpenTagPopover = () => {
    setIsTagPopoverOpen(true);
  };

  const handleOpenReplaceDialog = () => {
    setIsReplaceDialogOpen(true);
  };

  const handleReplaceFile = (file: File) => {
    if (onReplaceFile && item.uuid) {
      onReplaceFile(item.uuid, file);
      setIsReplaceDialogOpen(false);
    }
  };

  const getFileTypeDisplay = () => {
    if (item.type === "folder") return "Folder";

    // Use the fileType directly from the backend and capitalize first letter
    if (item.fileType) {
      return item.fileType.charAt(0).toUpperCase() + item.fileType.slice(1);
    }

    return "File";
  };

  const handleCheckboxClick = (e: React.MouseEvent) => {
    e.stopPropagation(); // Stop propagation to prevent the item click handler
    onToggleSelect?.();
  };

  // Render tags if present
  const renderTags = () => {
    if (!item.tags || item.tags.length === 0) return null;

    const tagsToShow =
      viewMode === "grid" ? item.tags.slice(0, 2) : item.tags.slice(0, 3);
    const hasMoreTags = item.tags.length > tagsToShow.length;

    return (
      <div className={styles.tagContainer}>
        {tagsToShow.map((tag) => (
          <span
            className={styles.tag}
            key={tag.id}
            style={{
              backgroundColor: `${tag.color}20`,
              color: tag.color || "",
            }}
          >
            {tag.name}
          </span>
        ))}
        {hasMoreTags && (
          <span className={styles.moreTag}>
            +{item.tags.length - tagsToShow.length}
          </span>
        )}
      </div>
    );
  };

  const renderContextMenu = () => (
    <ContextMenuContent className={styles.contextMenu}>
      {isSelectionMode ? (
        <ContextMenuItem
          className={styles.contextMenuItem}
          onClick={onToggleSelect}
        >
          {isSelected ? (
            <>
              <Square className={styles.contextMenuIcon} size={16} />
              Deselect
            </>
          ) : (
            <>
              <CheckSquare className={styles.contextMenuIcon} size={16} />
              Select
            </>
          )}
        </ContextMenuItem>
      ) : (
        <>
          <ContextMenuItem
            className={styles.contextMenuItem}
            onClick={handleShowDetails}
          >
            <Info className={styles.contextMenuIcon} size={16} />
            Details
          </ContextMenuItem>

          {/* Download option for both files and folders */}
          <ContextMenuItem
            className={styles.contextMenuItem}
            disabled={isDownloading}
            onClick={handleDownload}
          >
            {isDownloading ? (
              <Loader2
                className={`${styles.contextMenuIcon} ${styles.spinningIcon}`}
                size={16}
              />
            ) : (
              <Download className={styles.contextMenuIcon} size={16} />
            )}
            {isDownloading ? "Downloading..." : "Download"}
          </ContextMenuItem>

          {/* Replace option only for files */}
          {item.type === "file" && (
            <ContextMenuItem
              className={styles.contextMenuItem}
              onClick={handleOpenReplaceDialog}
            >
              <RefreshCw className={styles.contextMenuIcon} size={16} />
              Replace File
            </ContextMenuItem>
          )}

          <ContextMenuItem
            className={styles.contextMenuItem}
            onClick={handleStartRename}
          >
            <Edit2 className={styles.contextMenuIcon} size={16} />
            Rename
          </ContextMenuItem>

          <ContextMenuItem
            className={styles.contextMenuItem}
            onClick={handleOpenTagPopover}
          >
            <Tag className={styles.contextMenuIcon} size={16} />
            {item.tags && item.tags.length > 0 ? "Manage Tags" : "Add Tag"}
          </ContextMenuItem>

          <ContextMenuSeparator className={styles.contextMenuSeparator} />

          <ContextMenuItem
            className={`${styles.contextMenuItem} ${styles.destructiveMenuItem}`}
            onClick={handleDelete}
          >
            <Trash2 className={styles.contextMenuIcon} size={16} />
            Delete
          </ContextMenuItem>
        </>
      )}
    </ContextMenuContent>
  );

  // Render name (either editable input or static text)
  const renderName = () => {
    if (isRenaming) {
      return (
        <div
          className={
            viewMode === "grid"
              ? styles.renamingContainerGrid
              : styles.renamingContainerList
          }
          onClick={(e) => e.stopPropagation()}
        >
          <Input
            autoFocus
            className={styles.renameInput}
            disabled={isRenamingLoading}
            onBlur={handleInputBlur}
            onChange={(e) => setNewName(e.target.value)}
            onKeyDown={handleInputKeyDown}
            ref={inputRef}
            value={newName}
          />
          {isRenamingLoading && (
            <div className={styles.renameLoader}>
              <Loader2 className={`${styles.spinningIcon}`} size={16} />
            </div>
          )}
        </div>
      );
    } else {
      return (
        <div
          className={
            viewMode === "grid" ? styles.fileNameGrid : styles.fileNameList
          }
        >
          {item.name}
        </div>
      );
    }
  };

  // Custom checkbox with blue background and white checkmark
  const renderCheckbox = () => (
    <div className={styles.checkboxWrapper}>
      <Checkbox checked={isSelected} className={styles.checkbox} />
    </div>
  );

  // Grid view rendering
  if (viewMode === "grid") {
    return (
      <>
        <ContextMenu>
          <ContextMenuTrigger>
            <div
              className={`${styles.fileItemGrid} ${isSelected ? styles.selectedItem : ""} ${isRenaming ? styles.renamingItem : ""}`}
              onClick={handleClick}
            >
              {isSelectionMode && (
                <div
                  className={styles.checkboxPositioner}
                  onClick={handleCheckboxClick}
                >
                  {renderCheckbox()}
                </div>
              )}

              <div className={styles.fileContentGrid}>
                <div className={styles.fileIconContainerGrid}>
                  {getFileIcon()}
                </div>

                {renderName()}

                <div className={styles.fileMetaGrid}>
                  {item.fileSize !== undefined &&
                    formatFileSize(item.fileSize || 0)}
                </div>

                {renderTags()}
              </div>
            </div>
          </ContextMenuTrigger>
          {renderContextMenu()}
        </ContextMenu>

        {/* Tag Popover */}
        <TagPopover
          node={item}
          onOpenChange={setIsTagPopoverOpen}
          open={isTagPopoverOpen}
        />

        {/* Details Drawer */}
        <FileDetailsDrawer
          isOpen={isDetailsDrawerOpen}
          node={item}
          onClose={() => setIsDetailsDrawerOpen(false)}
        />

        {/* Replace File Dialog */}
        <ReplaceFileDialog
          isOpen={isReplaceDialogOpen}
          maxFileSize={100 * 1024 * 1024} // 100MB
          onClose={() => setIsReplaceDialogOpen(false)}
          onReplace={handleReplaceFile}
          originalFileName={item.name}
        />
      </>
    );
  } else {
    // List view rendering
    return (
      <>
        <ContextMenu>
          <ContextMenuTrigger>
            <div
              className={`${styles.fileItemList} ${isSelected ? styles.selectedItem : ""} ${isRenaming ? styles.renamingItem : ""}`}
              onClick={handleClick}
            >
              {isSelectionMode && (
                <div
                  className={styles.checkboxPositionerList}
                  onClick={handleCheckboxClick}
                >
                  {renderCheckbox()}
                </div>
              )}

              <div className={styles.fileContentList}>
                <div className={styles.fileIconContainerList}>
                  {getFileIcon()}
                </div>

                <div className={styles.fileDetailsContainer}>
                  {renderName()}

                  <div className={styles.fileMetaList}>
                    {getFileTypeDisplay()}
                    {item.fileSize !== undefined && (
                      <>
                        <span className={styles.metaSeparator}>•</span>
                        {formatFileSize(item.fileSize || 0)}
                      </>
                    )}
                    <span className={styles.metaSeparator}>•</span>
                    <span className={styles.dateInfo}>
                      <Clock className={styles.dateIcon} size={12} />
                      {formatDate(item.updatedAt)}
                    </span>
                  </div>

                  {renderTags()}
                </div>
              </div>

              <div className={styles.fileTypeColumn}>
                <FileType className={styles.columnIcon} size={14} />
                {getFileTypeDisplay()}
              </div>

              <div className={styles.dateColumn}>
                <Clock className={styles.columnIcon} size={14} />
                {formatDate(item.updatedAt)}
              </div>

              <div className={styles.sizeColumn}>
                {item.fileSize !== undefined
                  ? formatFileSize(item.fileSize || 0)
                  : "-"}
              </div>
            </div>
          </ContextMenuTrigger>
          {renderContextMenu()}
        </ContextMenu>

        {/* Tag Popover */}
        <TagPopover
          node={item}
          onOpenChange={setIsTagPopoverOpen}
          open={isTagPopoverOpen}
        />

        {/* Details Drawer */}
        <FileDetailsDrawer
          isOpen={isDetailsDrawerOpen}
          node={item}
          onClose={() => setIsDetailsDrawerOpen(false)}
        />

        {/* Replace File Dialog */}
        <ReplaceFileDialog
          isOpen={isReplaceDialogOpen}
          maxFileSize={100 * 1024 * 1024} // 100MB
          onClose={() => setIsReplaceDialogOpen(false)}
          onReplace={handleReplaceFile}
          originalFileName={item.name}
        />
      </>
    );
  }
};

export default FileItem;
