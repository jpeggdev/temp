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
  Info,
  FileType,
  Clock,
  Edit2,
  Loader2,
  AlertCircle,
} from "lucide-react";
import { FilesystemNode } from "../../api/listFolderContents/types";
import {
  ContextMenu,
  ContextMenuContent,
  ContextMenuItem,
  ContextMenuTrigger,
  ContextMenuSeparator,
} from "@/components/ui/context-menu";
import { formatFileSize } from "@/modules/hub/features/FileManagement/utils/formatters";
import { formatDate } from "@/utils/dateUtils";
import { downloadAndSaveFilesystemNode } from "../../api/downloadFilesystemNode/downloadFilesystemNodeApi";
import { downloadAndSaveMultipleNodes } from "../../api/downloadMultipleNodes/downloadMultipleNodesApi";
import { toast } from "@/components/ui/use-toast";
import { Input } from "@/components/ui/input";
import styles from "./FileItem.module.css";

interface FileItemPickerProps {
  item: FilesystemNode;
  viewMode: "grid" | "list";
  isSelected: boolean;
  onFolderClick: (uuid: string) => void;
  onClick: () => void;
  onShowDetails: (item: FilesystemNode) => void;
  onRename?: (nodeUuid: string, newName: string) => Promise<void>;
  allowedFileTypes?: string[]; // New prop for file type filtering
}

const FileItemPicker: React.FC<FileItemPickerProps> = ({
  item,
  viewMode,
  isSelected,
  onFolderClick,
  onClick,
  onShowDetails,
  onRename,
  allowedFileTypes,
}) => {
  const [isDownloading, setIsDownloading] = useState<boolean>(false);
  const [isRenaming, setIsRenaming] = useState<boolean>(false);
  const [newName, setNewName] = useState<string>(item.name);
  const [isRenamingLoading, setIsRenamingLoading] = useState<boolean>(false);
  const inputRef = useRef<HTMLInputElement>(null);
  const renameContainerRef = useRef<HTMLDivElement>(null);

  // Check if this file is selectable based on allowed file types
  const isSelectable = (): boolean => {
    // Folders are always selectable
    if (item.type === "folder") return true;

    // If no file type restrictions, everything is selectable
    if (!allowedFileTypes || allowedFileTypes.length === 0) return true;

    // Check if the file's mime type matches any allowed type
    return allowedFileTypes.some((allowedType) => {
      // If the allowed type ends with *, do a prefix match (e.g., "image/*")
      if (allowedType.endsWith("*")) {
        const prefix = allowedType.slice(0, -1);
        return item.mimeType?.startsWith(prefix) || false;
      }
      // Otherwise do an exact match
      return item.mimeType === allowedType;
    });
  };

  const selectable = isSelectable();

  // Focus the input when renaming starts
  useEffect(() => {
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

  // Add event listener to handle clicks outside the rename container
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        isRenaming &&
        renameContainerRef.current &&
        !renameContainerRef.current.contains(event.target as Node)
      ) {
        // Save on click outside, but only if the name has actually changed
        if (newName.trim() !== "" && newName !== item.name) {
          handleSaveRename();
        } else {
          handleCancelRename();
        }
      }
    };

    if (isRenaming) {
      document.addEventListener("mousedown", handleClickOutside);
    }

    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, [isRenaming, newName, item.name]);

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
    if (!selectable && item.type === "file") return; // Prevent actions on non-selectable files

    if (item.type === "folder") {
      onFolderClick(item.uuid);
    } else {
      onClick();
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

    if (!onRename || newName.trim() === "" || newName === item.name) {
      handleCancelRename();
      return;
    }

    setIsRenamingLoading(true);
    try {
      await onRename(item.uuid, newName);
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
    e.stopPropagation();
  };

  const handleShowDetails = () => {
    onShowDetails(item);
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

  const getFileTypeDisplay = () => {
    if (item.type === "folder") return "Folder";

    // Use the fileType directly from the backend and capitalize first letter
    if (item.fileType) {
      return item.fileType.charAt(0).toUpperCase() + item.fileType.slice(1);
    }

    return "File";
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
      <ContextMenuItem
        className={styles.contextMenuItem}
        onClick={handleShowDetails}
      >
        <Info className={styles.contextMenuIcon} size={16} />
        Details
      </ContextMenuItem>

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

      {onRename && (
        <>
          <ContextMenuSeparator className={styles.contextMenuSeparator} />
          <ContextMenuItem
            className={styles.contextMenuItem}
            onClick={handleStartRename}
          >
            <Edit2 className={styles.contextMenuIcon} size={16} />
            Rename
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
          ref={renameContainerRef}
        >
          <Input
            autoFocus
            className={styles.renameInput}
            disabled={isRenamingLoading}
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

  // Add disabled class if file is not selectable
  const getFileItemClass = () => {
    const baseClass =
      viewMode === "grid" ? styles.fileItemGrid : styles.fileItemList;
    const selectedClass = isSelected ? styles.selectedItem : "";
    const renamingClass = isRenaming ? styles.renamingItem : "";
    const disabledClass =
      !selectable && item.type === "file" ? styles.disabledItem : "";

    return `${baseClass} ${selectedClass} ${renamingClass} ${disabledClass}`;
  };

  // Grid view rendering
  if (viewMode === "grid") {
    return (
      <ContextMenu>
        <ContextMenuTrigger>
          <div
            className={getFileItemClass()}
            onClick={handleClick}
            onDoubleClick={handleShowDetails}
          >
            <div className={styles.fileContentGrid}>
              <div className={styles.fileIconContainerGrid}>
                {getFileIcon()}
                {!selectable && item.type === "file" && (
                  <div className={styles.nonSelectableOverlay}>
                    <AlertCircle
                      className={styles.nonSelectableIcon}
                      size={24}
                    />
                  </div>
                )}
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
    );
  } else {
    // List view rendering
    return (
      <ContextMenu>
        <ContextMenuTrigger>
          <div
            className={getFileItemClass()}
            onClick={handleClick}
            onDoubleClick={handleShowDetails}
          >
            <div className={styles.fileContentList}>
              <div className={styles.fileIconContainerList}>
                {getFileIcon()}
                {!selectable && item.type === "file" && (
                  <div className={styles.nonSelectableOverlay}>
                    <AlertCircle
                      className={styles.nonSelectableIcon}
                      size={16}
                    />
                  </div>
                )}
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
    );
  }
};

export default FileItemPicker;
