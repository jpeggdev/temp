// src/modules/hub/features/FileManagement/components/FilterSidebar/FilePickerFilterSidebar.tsx
import React from "react";
import {
  Filter,
  X,
  Search,
  Tag as TagIcon,
  FileType,
  Check,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Checkbox } from "@/components/ui/checkbox";
import { FileTypeWithIcon } from "../../utils/fileTypeIcons";
import { TagStatDTO } from "../../api/getFileManagerMetaData/types";
import { Tag } from "../../data/filterData"; // For backwards compatibility
import styles from "./FilterSidebar.module.css";

interface FilterSidebarProps {
  selectedFileTypes: string[];
  selectedTags: number[];
  toggleFileType: (typeId: string) => void;
  toggleTag: (tagId: number) => void;
  clearAllFilters: () => void;
  activeFiltersCount: number;
  filterType: string | null;
  searchInput: string;
  handleFilterChange: (type: string | null) => void;
  handleSearch: (e: React.ChangeEvent<HTMLInputElement>) => void;
  fileTypes: FileTypeWithIcon[] | any[]; // Accept both new and old format
  tags: TagStatDTO[] | Tag[]; // Accept both new and old format
  isOpen: boolean;
  onClose: () => void;
  isLoading?: boolean;
}

const FilterSidebar: React.FC<FilterSidebarProps> = ({
  selectedFileTypes,
  selectedTags,
  toggleFileType,
  toggleTag,
  clearAllFilters,
  activeFiltersCount,
  filterType,
  searchInput,
  handleFilterChange,
  handleSearch,
  fileTypes,
  tags,
  isOpen,
  onClose,
  isLoading = false,
}) => {
  const handleClearSearch = () => {
    handleSearch({
      target: { value: "" },
    } as React.ChangeEvent<HTMLInputElement>);
  };

  return (
    <>
      {/* Backdrop for mobile */}
      {isOpen && <div className={styles.backdrop} onClick={onClose} />}

      {/* Sidebar */}
      <aside
        className={`${styles.sidebar} ${isOpen ? styles.sidebarOpen : styles.sidebarClosed}`}
      >
        <div className={styles.sidebarHeader}>
          <h3 className={styles.sidebarTitle}>
            <Filter className={styles.titleIcon} size={18} />
            Filters
            {activeFiltersCount > 0 && (
              <Badge className={styles.filterBadge}>{activeFiltersCount}</Badge>
            )}
          </h3>
          <Button
            className={styles.closeButton}
            onClick={onClose}
            size="icon"
            variant="ghost"
          >
            <X size={18} />
          </Button>
        </div>

        {/* Active filters */}
        {(filterType || searchInput || activeFiltersCount > 0) && (
          <div className={styles.activeFiltersContainer}>
            <h4 className={styles.sectionTitle}>Active filters</h4>
            <div className={styles.activeFiltersList}>
              {searchInput && (
                <span className={styles.activeFilterTag}>
                  <Search className={styles.filterTagIcon} size={12} />
                  {searchInput}
                  <button
                    className={styles.removeFilterButton}
                    onClick={handleClearSearch}
                  >
                    <X size={14} />
                  </button>
                </span>
              )}

              {filterType && (
                <span className={styles.activeFilterTag}>
                  <FileType className={styles.filterTagIcon} size={12} />
                  {filterType.charAt(0).toUpperCase() + filterType.slice(1)}
                  <button
                    className={styles.removeFilterButton}
                    onClick={() => handleFilterChange(null)}
                  >
                    <X size={14} />
                  </button>
                </span>
              )}
            </div>
            {(filterType || searchInput || activeFiltersCount > 0) && (
              <Button
                className={styles.clearFiltersButton}
                onClick={clearAllFilters}
                size="sm"
                variant="outline"
              >
                Clear all filters
              </Button>
            )}
          </div>
        )}

        <div className={styles.divider} />

        {isLoading ? (
          <div className={styles.loadingState}>
            <div className={styles.loadingSpinner}></div>
            <span>Loading filters...</span>
          </div>
        ) : (
          <>
            {/* File Types */}
            <div className={styles.filterSection}>
              <h4 className={styles.sectionTitle}>
                <FileType className={styles.sectionIcon} size={16} />
                File Types
              </h4>
              <div className={styles.filterOptions}>
                {fileTypes.map((type) => (
                  <div className={styles.filterOption} key={type.id}>
                    <Checkbox
                      checked={selectedFileTypes.includes(type.id)}
                      className={styles.checkbox}
                      color="blue-gradient" // Using our new color variant
                      icon={<Check className="h-3.5 w-3.5 text-white" />}
                      id={`sidebar-type-${type.id}`}
                      onCheckedChange={() => toggleFileType(type.id)}
                      radius="md" // Slightly rounded corners
                      size="sm" // Smaller size to match the design
                    />
                    <label
                      className={styles.filterLabel}
                      htmlFor={`sidebar-type-${type.id}`}
                    >
                      <span className={styles.filterLabelContent}>
                        <span className={styles.iconWrapper}>{type.icon}</span>
                        {type.name}
                      </span>
                      {"count" in type && (
                        <span className={styles.filterCount}>{type.count}</span>
                      )}
                    </label>
                  </div>
                ))}
              </div>
            </div>

            <div className={styles.divider} />

            {/* Tags */}
            <div className={styles.filterSection}>
              <h4 className={styles.sectionTitle}>
                <TagIcon className={styles.sectionIcon} size={16} />
                Tags
              </h4>
              {tags.length === 0 ? (
                <div className={styles.emptyState}>No tags available</div>
              ) : (
                <div className={styles.filterOptions}>
                  {tags.map((tag) => (
                    <div
                      className={styles.tagOption}
                      key={tag.id}
                      onClick={() => toggleTag(tag.id)}
                    >
                      <div
                        className={`${styles.tagCheckbox} ${
                          selectedTags.includes(tag.id)
                            ? styles.tagCheckboxChecked
                            : ""
                        }`}
                      >
                        {selectedTags.includes(tag.id) && (
                          <div
                            className={styles.tagCheckboxDot}
                            style={{ backgroundColor: tag.color || "#6b7280" }}
                          />
                        )}
                      </div>
                      <label className={styles.tagLabel}>
                        <div className={styles.tagLabelContent}>
                          <div
                            className={styles.tagColorDot}
                            style={{ backgroundColor: tag.color || "#6b7280" }}
                          />
                          {tag.name}
                        </div>
                        {"count" in tag && (
                          <span className={styles.tagCount}>{tag.count}</span>
                        )}
                      </label>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </>
        )}
      </aside>
    </>
  );
};

export default FilterSidebar;
