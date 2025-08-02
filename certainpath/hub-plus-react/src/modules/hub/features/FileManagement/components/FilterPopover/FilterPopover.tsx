// src/modules/hub/features/FileManagement/components/FilterPopover/FilterPopover.tsx
import React, { useRef, useEffect } from "react";
import { X, FileType, Check } from "lucide-react";
import { Button } from "@/components/ui/button";
import { FileTypeWithIcon } from "../../utils/fileTypeIcons";
import { TagStatDTO } from "../../api/getFileManagerMetaData/types";
import { Tag } from "../../data/filterData";
import styles from "./FilterPopover.module.css";

interface FilterPopoverProps {
  selectedFileTypes: string[];
  selectedTags: number[];
  toggleFileType: (typeId: string) => void;
  toggleTag: (tagId: number) => void;
  clearAllFilters: () => void;
  activeFiltersCount: number;
  fileTypes: FileTypeWithIcon[] | any[];
  tags: TagStatDTO[] | Tag[];
  onClose: () => void;
  isLoading?: boolean;
}

const FilterPopover: React.FC<FilterPopoverProps> = ({
  selectedFileTypes,
  selectedTags,
  toggleFileType,
  toggleTag,
  clearAllFilters,
  activeFiltersCount,
  fileTypes,
  tags,
  onClose,
}) => {
  const popoverRef = useRef<HTMLDivElement>(null);

  // Close when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        popoverRef.current &&
        !popoverRef.current.contains(event.target as Node)
      ) {
        onClose();
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, [onClose]);

  return (
    <div className={styles.overlay}>
      <div className={styles.popover} ref={popoverRef}>
        <div className={styles.header}>
          <h3 className={styles.title}>
            <FileType className={styles.titleIcon} size={16} />
            Filter by Type & Tags
          </h3>
          <button className={styles.closeButton} onClick={onClose}>
            <X size={18} />
          </button>
        </div>

        {activeFiltersCount > 0 && (
          <div className={styles.activeBadge}>
            <span className={styles.activeCount}>
              {activeFiltersCount} active
            </span>
            <button className={styles.clearButton} onClick={clearAllFilters}>
              Clear All
            </button>
          </div>
        )}

        <div className={styles.content}>
          {/* File Types Section */}
          <div className={styles.section}>
            <h4 className={styles.sectionTitle}>File Types</h4>
            <div className={styles.typeGrid}>
              {fileTypes.map((type) => (
                <div
                  className={`${styles.typeItem} ${
                    selectedFileTypes.includes(type.id) ? styles.selected : ""
                  }`}
                  key={type.id}
                  onClick={() => toggleFileType(type.id)}
                >
                  <span className={styles.typeIcon}>{type.icon}</span>
                  <span className={styles.typeName}>{type.name}</span>
                  {selectedFileTypes.includes(type.id) && (
                    <Check className={styles.checkIcon} size={14} />
                  )}
                </div>
              ))}
            </div>
          </div>

          {/* Tags Section */}
          <div className={styles.section}>
            <h4 className={styles.sectionTitle}>Tags</h4>
            {tags.length === 0 ? (
              <div className={styles.emptyTags}>No tags available</div>
            ) : (
              <div className={styles.tagList}>
                {tags.map((tag) => (
                  <div
                    className={`${styles.tagItem} ${
                      selectedTags.includes(tag.id) ? styles.selected : ""
                    }`}
                    key={tag.id}
                    onClick={() => toggleTag(tag.id)}
                    style={{
                      backgroundColor: selectedTags.includes(tag.id)
                        ? `${tag.color}15`
                        : "transparent",
                      borderColor: tag.color || "#6b7280",
                    }}
                  >
                    <span
                      className={styles.tagDot}
                      style={{ backgroundColor: tag.color || "#6b7280" }}
                    ></span>
                    <span
                      className={styles.tagName}
                      style={{ color: tag.color || "#6b7280" }}
                    >
                      {tag.name}
                    </span>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        <div className={styles.footer}>
          <Button className={styles.doneButton} onClick={onClose}>
            Done
          </Button>
        </div>
      </div>
    </div>
  );
};

export default FilterPopover;
