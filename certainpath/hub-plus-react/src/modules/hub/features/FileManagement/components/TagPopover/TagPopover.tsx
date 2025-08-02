import React, { useState, useEffect, Fragment } from "react";
import { Popover, Transition } from "@headlessui/react";
import {
  Plus,
  Loader2,
  X,
  Tag,
  Search,
  ChevronRight,
  CheckCircle2,
} from "lucide-react";
import { FilesystemNode } from "../../api/listFolderContents/types";
import { useDispatch, useSelector } from "react-redux";
import { toast } from "@/components/ui/use-toast";
import {
  assignTagToNodeAction,
  createAndAssignTagAction,
  listTagsAction,
  removeTagFromNodeAction,
} from "@/modules/hub/features/FileManagement/slices/fileManagementTagSlice";
import { AppDispatch } from "@/app/store";
import { RootState } from "@/app/rootReducer";
import {
  addTagToNode,
  assignTagToNode,
  removeTagFromNode,
} from "../../slices/fileManagementSlice";
import styles from "./TagPopover.module.css";
import {
  addNewTag,
  decrementTagCount,
  incrementTagCount,
} from "@/modules/hub/features/FileManagement/slices/fileManagerMetadataSlice";

interface TagPopoverProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  node: FilesystemNode;
  onTagsUpdated?: () => void;
  triggerRef?: React.RefObject<HTMLElement>;
}

const colorOptions = [
  { name: "Red", value: "#ef4444" },
  { name: "Orange", value: "#f97316" },
  { name: "Amber", value: "#f59e0b" },
  { name: "Yellow", value: "#eab308" },
  { name: "Lime", value: "#84cc16" },
  { name: "Green", value: "#22c55e" },
  { name: "Emerald", value: "#10b981" },
  { name: "Teal", value: "#14b8a6" },
  { name: "Cyan", value: "#06b6d4" },
  { name: "Sky", value: "#0ea5e9" },
  { name: "Blue", value: "#3b82f6" },
  { name: "Indigo", value: "#6366f1" },
  { name: "Violet", value: "#8b5cf6" },
  { name: "Purple", value: "#a855f7" },
  { name: "Fuchsia", value: "#d946ef" },
  { name: "Pink", value: "#ec4899" },
  { name: "Rose", value: "#f43f5e" },
  { name: "Gray", value: "#6b7280" },
];

const TagPopover: React.FC<TagPopoverProps> = ({
  open,
  onOpenChange,
  node,
  onTagsUpdated,
}) => {
  const dispatch = useDispatch<AppDispatch>();
  const { tags, listTagsLoading, createTagLoading } = useSelector(
    (state: RootState) => state.fileManagementTag,
  );

  const [searchTerm, setSearchTerm] = useState("");
  const [selectedColor, setSelectedColor] = useState(colorOptions[9].value); // Default to sky blue
  const [processingTagId, setProcessingTagId] = useState<number | null>(null);

  // Load tags when popover opens
  useEffect(() => {
    if (open) {
      dispatch(listTagsAction());
    }
  }, [open, dispatch]);

  // Reset search when popover closes
  useEffect(() => {
    if (!open) {
      setSearchTerm("");
      setProcessingTagId(null);
    }
  }, [open]);

  // Filter tags based on search term
  const filteredTags = tags.filter((tag) =>
    tag.name.toLowerCase().includes(searchTerm.toLowerCase()),
  );

  // Check if we need to show the "create tag" option
  const showCreateTag =
    searchTerm.trim() !== "" &&
    !filteredTags.some(
      (tag) => tag.name.toLowerCase() === searchTerm.toLowerCase(),
    );

  const handleCreateTag = async () => {
    if (!searchTerm.trim()) return;

    try {
      const result = await dispatch(
        createAndAssignTagAction({
          name: searchTerm.trim(),
          color: selectedColor,
          filesystemNodeUuid: node.uuid,
        }),
      );

      // Update the Redux state directly
      if (result && result.data) {
        // Update file management state to add tag to the file
        dispatch(addTagToNode(result.data));

        // Add the new tag to metadata without refetching
        dispatch(addNewTag(result.data));
      }

      toast({
        title: "Tag created",
        description: `Tag "${searchTerm}" has been created and assigned`,
      });

      // Clear the search term but keep the popover open for additional tags
      setSearchTerm("");
      if (onTagsUpdated) {
        onTagsUpdated();
      }
    } catch {
      toast({
        title: "Error",
        description: "Failed to create and assign tag",
        variant: "destructive",
      });
    }
  };

  const handleToggleTag = async (
    tagId: number,
    tagName: string,
    isAssigned: boolean,
  ) => {
    try {
      setProcessingTagId(tagId);

      if (isAssigned) {
        // Remove tag
        await dispatch(
          removeTagFromNodeAction({
            tagId,
            filesystemNodeUuid: node.uuid,
          }),
        );

        // Update the Redux state directly
        dispatch(
          removeTagFromNode({
            tagId,
            filesystemNodeUuid: node.uuid,
          }),
        );

        // Decrement the tag count in metadata
        dispatch(decrementTagCount(tagId));

        toast({
          title: "Tag removed",
          description: `Tag "${tagName}" has been removed`,
        });
      } else {
        // Assign tag
        const result = await dispatch(
          assignTagToNodeAction({
            tagId,
            filesystemNodeUuid: node.uuid,
          }),
        );

        // Update the Redux state directly
        if (result && result.data) {
          dispatch(assignTagToNode(result.data));

          // Increment the tag count in metadata
          dispatch(incrementTagCount(tagId));
        }

        toast({
          title: "Tag assigned",
          description: `Tag "${tagName}" has been assigned`,
        });
      }

      if (onTagsUpdated) {
        onTagsUpdated();
      }
    } catch {
      toast({
        title: "Error",
        description: isAssigned
          ? "Failed to remove tag"
          : "Failed to assign tag",
        variant: "destructive",
      });
    } finally {
      setProcessingTagId(null);
    }
  };

  const handleInputKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter" && showCreateTag) {
      handleCreateTag();
    }
  };

  return (
    <Popover className={styles.popoverWrapper}>
      {/* Hidden trigger that's controlled externally */}
      <span className="sr-only">Open tag popover</span>

      <Transition
        as={Fragment}
        enter="transition ease-out duration-200"
        enterFrom="opacity-0 translate-y-1"
        enterTo="opacity-100 translate-y-0"
        leave="transition ease-in duration-150"
        leaveFrom="opacity-100 translate-y-0"
        leaveTo="opacity-0 translate-y-1"
        show={open}
      >
        <Popover.Panel className={styles.popoverPanel} static>
          {/* Header */}
          <div className={styles.popoverHeader}>
            <div className={styles.headerTitle}>
              <Tag className={styles.headerIcon} size={18} />
              <h3>Manage Tags</h3>
            </div>
            <button
              aria-label="Close"
              className={styles.closeButton}
              onClick={() => onOpenChange(false)}
            >
              <X size={16} />
            </button>
          </div>

          {/* Search input */}
          <div className={styles.searchContainer}>
            <div className={styles.searchInputWrapper}>
              <Search className={styles.searchIcon} size={16} />
              <input
                autoFocus
                className={styles.searchInput}
                onChange={(e) => setSearchTerm(e.target.value)}
                onKeyDown={handleInputKeyDown}
                placeholder="Search or create a tag..."
                type="text"
                value={searchTerm}
              />
            </div>
          </div>

          {/* Tag list container */}
          <div className={styles.tagListContainer}>
            {listTagsLoading ? (
              <div className={styles.loadingContainer}>
                <Loader2 className={styles.spinningIcon} size={20} />
                <span>Loading tags...</span>
              </div>
            ) : (
              <>
                {/* Assigned tags section */}
                {node.tags && node.tags.length > 0 && (
                  <div className={styles.tagSection}>
                    <div className={styles.tagSectionHeader}>
                      <h4>Assigned Tags</h4>
                      <span className={styles.tagCount}>
                        {node.tags.length}
                      </span>
                    </div>
                    <div className={styles.tagList}>
                      {node.tags.map((tag) => {
                        const isProcessing = processingTagId === tag.id;

                        return (
                          <div
                            className={`${styles.tagItem} ${styles.assignedTag} ${isProcessing ? styles.processingTag : ""}`}
                            key={tag.id}
                            onClick={() =>
                              !isProcessing &&
                              handleToggleTag(tag.id, tag.name, true)
                            }
                          >
                            <div className={styles.tagInfo}>
                              <div
                                className={styles.tagColorDot}
                                style={{
                                  backgroundColor: tag.color || "#6b7280",
                                }}
                              />
                              <span className={styles.tagName}>{tag.name}</span>
                            </div>

                            {isProcessing ? (
                              <Loader2
                                className={styles.spinningIcon}
                                size={16}
                              />
                            ) : (
                              <div className={styles.actionIconsContainer}>
                                <CheckCircle2
                                  className={`${styles.checkIcon} ${styles.visibleIcon}`}
                                  size={16}
                                />
                                <X
                                  className={`${styles.removeIcon} ${styles.hiddenIcon}`}
                                  size={16}
                                />
                              </div>
                            )}
                          </div>
                        );
                      })}
                    </div>
                  </div>
                )}

                {/* Available tags section */}
                {filteredTags.length > 0 && (
                  <div className={styles.tagSection}>
                    <div className={styles.tagSectionHeader}>
                      <h4>
                        {node.tags && node.tags.length > 0
                          ? "Available Tags"
                          : "All Tags"}
                      </h4>
                      <span className={styles.tagCount}>
                        {filteredTags.length}
                      </span>
                    </div>
                    <div className={styles.tagList}>
                      {filteredTags.map((tag) => {
                        // Skip tags that are already assigned
                        const isAssigned = node.tags?.some(
                          (t) => t.id === tag.id,
                        );
                        if (isAssigned) return null;

                        const isProcessing = processingTagId === tag.id;

                        return (
                          <div
                            className={`${styles.tagItem} ${isProcessing ? styles.processingTag : ""}`}
                            key={tag.id}
                            onClick={() =>
                              !isProcessing &&
                              handleToggleTag(tag.id, tag.name, false)
                            }
                          >
                            <div className={styles.tagInfo}>
                              <div
                                className={styles.tagColorDot}
                                style={{
                                  backgroundColor: tag.color || "#6b7280",
                                }}
                              />
                              <span className={styles.tagName}>{tag.name}</span>
                            </div>

                            {isProcessing ? (
                              <Loader2
                                className={styles.spinningIcon}
                                size={16}
                              />
                            ) : (
                              <Plus className={styles.addIcon} size={16} />
                            )}
                          </div>
                        );
                      })}
                    </div>
                  </div>
                )}

                {/* Create new tag section */}
                {showCreateTag && (
                  <div
                    className={`${styles.tagSection} ${styles.createTagSection}`}
                  >
                    <div className={styles.tagSectionHeader}>
                      <h4>Create New Tag</h4>
                      <ChevronRight className={styles.chevronIcon} size={16} />
                    </div>

                    <div className={styles.createTagContent}>
                      <div className={styles.newTagPreview}>
                        <div
                          className={styles.newTagColorPreview}
                          style={{ backgroundColor: selectedColor }}
                        />
                        <span className={styles.newTagName}>{searchTerm}</span>
                      </div>

                      <div className={styles.colorPaletteContainer}>
                        <h5 className={styles.colorPaletteTitle}>
                          Select Color
                        </h5>
                        <div className={styles.colorPalette}>
                          {colorOptions.map((color) => (
                            <button
                              className={`${styles.colorOption} ${selectedColor === color.value ? styles.selectedColor : ""}`}
                              key={color.value}
                              onClick={() => setSelectedColor(color.value)}
                              style={{ backgroundColor: color.value }}
                              title={color.name}
                              type="button"
                            />
                          ))}
                        </div>
                      </div>

                      <button
                        className={`${styles.createTagButton} ${createTagLoading ? styles.loadingButton : ""}`}
                        disabled={createTagLoading}
                        onClick={handleCreateTag}
                        type="button"
                      >
                        {createTagLoading ? (
                          <Loader2 className={styles.spinningIcon} size={16} />
                        ) : (
                          <Plus className={styles.createButtonIcon} size={16} />
                        )}
                        Create and Assign Tag
                      </button>
                    </div>
                  </div>
                )}

                {/* No results message */}
                {filteredTags.length === 0 && !showCreateTag && (
                  <div className={styles.emptyStateContainer}>
                    <Tag className={styles.emptyStateIcon} size={24} />
                    <p className={styles.emptyStateText}>
                      {searchTerm
                        ? "No tags found. Type to create a new tag."
                        : "No tags available. Type to create one."}
                    </p>
                  </div>
                )}
              </>
            )}
          </div>

          {/* Footer */}
          <div className={styles.popoverFooter}>
            <button
              className={styles.doneButton}
              onClick={() => onOpenChange(false)}
              type="button"
            >
              Done
            </button>
          </div>
        </Popover.Panel>
      </Transition>
    </Popover>
  );
};

export default TagPopover;
