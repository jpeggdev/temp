// src/modules/hub/features/FileManagement/components/FilterSidebar/FilePickerFilterSidebar.tsx
import React, { useState } from "react";
import {
  Filter,
  X,
  Search,
  Tag as TagIcon,
  FileType,
  Check,
  ChevronDown,
  ChevronRight,
} from "lucide-react";
import { FileTypeWithIcon } from "../../utils/fileTypeIcons";
import { TagStatDTO } from "../../api/getFileManagerMetaData/types";
import { Tag } from "../../data/filterData"; // For backwards compatibility
import styles from "./FilePickerFilterSidebar.module.css";

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

const FilePickerFilterSidebar: React.FC<FilterSidebarProps> = ({
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
  // State to track which sections are collapsed
  const [collapsedSections, setCollapsedSections] = useState<{
    activeFilters: boolean;
    fileTypes: boolean;
    tags: boolean;
  }>({
    activeFilters: false,
    fileTypes: false,
    tags: false,
  });

  const handleClearSearch = () => {
    handleSearch({
      target: { value: "" },
    } as React.ChangeEvent<HTMLInputElement>);
  };

  const hasActiveFilters = filterType || searchInput || activeFiltersCount > 0;

  // Function to toggle section collapse state
  const toggleSection = (section: keyof typeof collapsedSections) => {
    setCollapsedSections({
      ...collapsedSections,
      [section]: !collapsedSections[section],
    });
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
            <Filter className={styles.titleIcon} size={16} />
            Filters
            {activeFiltersCount > 0 && (
              <span className={styles.filterBadge}>{activeFiltersCount}</span>
            )}
          </h3>
          <button
            className={styles.closeButton}
            onClick={onClose}
            type="button"
          >
            <X size={16} />
          </button>
        </div>

        {/* Active filters */}
        {hasActiveFilters && (
          <div className={styles.filterSection}>
            <div
              className={styles.sectionHeader}
              onClick={() => toggleSection("activeFilters")}
            >
              <h4 className={styles.sectionTitle}>
                Active Filters
                <span className={styles.collapseIconContainer}>
                  {collapsedSections.activeFilters ? (
                    <ChevronRight className={styles.collapseIcon} size={12} />
                  ) : (
                    <ChevronDown className={styles.collapseIcon} size={12} />
                  )}
                </span>
              </h4>
            </div>

            <div
              className={`${styles.sectionContent} ${collapsedSections.activeFilters ? styles.collapsed : ""}`}
            >
              <div className={styles.activeFiltersContainer}>
                <div className={styles.activeFiltersList}>
                  {searchInput && (
                    <div className={styles.activeFilterTag}>
                      <Search className={styles.filterTagIcon} size={12} />
                      <span className={styles.filterTagText}>
                        {searchInput}
                      </span>
                      <button
                        className={styles.removeFilterButton}
                        onClick={handleClearSearch}
                      >
                        <X size={14} />
                      </button>
                    </div>
                  )}

                  {filterType && (
                    <div className={styles.activeFilterTag}>
                      <FileType className={styles.filterTagIcon} size={12} />
                      <span className={styles.filterTagText}>
                        {filterType.charAt(0).toUpperCase() +
                          filterType.slice(1)}
                      </span>
                      <button
                        className={styles.removeFilterButton}
                        onClick={() => handleFilterChange(null)}
                      >
                        <X size={14} />
                      </button>
                    </div>
                  )}
                </div>
                <button
                  className={styles.clearFiltersButton}
                  onClick={clearAllFilters}
                  type="button"
                >
                  Clear All Filters
                </button>
              </div>
            </div>
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
              <div
                className={styles.sectionHeader}
                onClick={() => toggleSection("fileTypes")}
              >
                <h4 className={styles.sectionTitle}>
                  <FileType className={styles.sectionIcon} size={14} />
                  File Types
                  <span className={styles.collapseIconContainer}>
                    {collapsedSections.fileTypes ? (
                      <ChevronRight className={styles.collapseIcon} size={12} />
                    ) : (
                      <ChevronDown className={styles.collapseIcon} size={12} />
                    )}
                  </span>
                </h4>
              </div>

              <div
                className={`${styles.sectionContent} ${collapsedSections.fileTypes ? styles.collapsed : ""}`}
              >
                <div className={styles.filterOptions}>
                  {fileTypes.map((type) => (
                    <div
                      className={`${styles.filterOption} ${selectedFileTypes.includes(type.id) ? styles.filterOptionSelected : ""}`}
                      key={type.id}
                      onClick={() => toggleFileType(type.id)}
                    >
                      <div className={styles.checkboxContainer}>
                        <div
                          className={`${styles.checkbox} ${selectedFileTypes.includes(type.id) ? styles.checkboxSelected : ""}`}
                        >
                          {selectedFileTypes.includes(type.id) && (
                            <Check className={styles.checkIcon} size={11} />
                          )}
                        </div>
                      </div>
                      <div className={styles.filterLabel}>
                        <div className={styles.filterLabelContent}>
                          <span className={styles.iconWrapper}>
                            {type.icon}
                          </span>
                          <span className={styles.typeName}>{type.name}</span>
                        </div>
                        {"count" in type && type.count > 0 && (
                          <span className={styles.filterCount}>
                            {type.count}
                          </span>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            <div className={styles.divider} />

            {/* Tags */}
            <div className={styles.filterSection}>
              <div
                className={styles.sectionHeader}
                onClick={() => toggleSection("tags")}
              >
                <h4 className={styles.sectionTitle}>
                  <TagIcon className={styles.sectionIcon} size={14} />
                  Tags
                  <span className={styles.collapseIconContainer}>
                    {collapsedSections.tags ? (
                      <ChevronRight className={styles.collapseIcon} size={12} />
                    ) : (
                      <ChevronDown className={styles.collapseIcon} size={12} />
                    )}
                  </span>
                </h4>
              </div>

              <div
                className={`${styles.sectionContent} ${collapsedSections.tags ? styles.collapsed : ""}`}
              >
                {tags.length === 0 ? (
                  <div className={styles.emptyState}>No tags available</div>
                ) : (
                  <div className={styles.filterOptions}>
                    {tags.map((tag) => (
                      <div
                        className={`${styles.tagOption} ${selectedTags.includes(tag.id) ? styles.tagOptionSelected : ""}`}
                        key={tag.id}
                        onClick={() => toggleTag(tag.id)}
                      >
                        <div className={styles.checkboxContainer}>
                          <div
                            className={`${styles.checkbox} ${selectedTags.includes(tag.id) ? styles.checkboxSelected : ""}`}
                          >
                            {selectedTags.includes(tag.id) && (
                              <Check className={styles.checkIcon} size={11} />
                            )}
                          </div>
                        </div>
                        <div className={styles.tagLabel}>
                          <div className={styles.tagLabelContent}>
                            <div
                              className={styles.tagColorDot}
                              style={{
                                backgroundColor: tag.color || "#6b7280",
                              }}
                            />
                            <span className={styles.tagName}>{tag.name}</span>
                          </div>
                          {"count" in tag && tag.count > 0 && (
                            <span className={styles.tagCount}>{tag.count}</span>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </>
        )}
      </aside>
    </>
  );
};

export default FilePickerFilterSidebar;
