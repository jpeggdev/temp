// src/modules/hub/features/FileManagement/components/FilterPanel/FilterPanel.tsx
import React from "react";
import { Filter, ChevronDown } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Checkbox } from "@/components/ui/checkbox";

// File types and tags type definitions
interface FileType {
  id: string;
  name: string;
  icon: React.ReactNode;
}

interface Tag {
  id: number;
  name: string;
  color: string;
}

interface FilterPanelProps {
  isFilterExpanded: boolean;
  setIsFilterExpanded: (expanded: boolean) => void;
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
  fileTypes: FileType[];
  tags: Tag[];
}

const FilterPanel: React.FC<FilterPanelProps> = ({
  isFilterExpanded,
  setIsFilterExpanded,
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
}) => {
  return (
    <>
      {/* Filter Accordion */}
      <div className="mb-6">
        <Button
          className="flex items-center gap-2 mb-3"
          onClick={() => setIsFilterExpanded(!isFilterExpanded)}
          variant="outline"
        >
          <Filter size={16} />
          Filters
          {activeFiltersCount > 0 && (
            <Badge className="ml-2">{activeFiltersCount}</Badge>
          )}
          <ChevronDown
            className={
              isFilterExpanded
                ? "rotate-180 transition-transform"
                : "transition-transform"
            }
            size={16}
          />
        </Button>

        {isFilterExpanded && (
          <div className="mt-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 animate-in fade-in-50 slide-in-from-top-5 duration-200">
            <div className="flex flex-col md:flex-row gap-6">
              <div className="md:w-1/2">
                <h4 className="text-sm font-semibold mb-3">File Types</h4>
                <div className="grid grid-cols-2 gap-y-2">
                  {fileTypes.map((type) => (
                    <div className="flex items-center" key={type.id}>
                      <Checkbox
                        checked={selectedFileTypes.includes(type.id)}
                        id={`type-${type.id}`}
                        onCheckedChange={() => toggleFileType(type.id)}
                      />
                      <label
                        className="ml-2 text-sm cursor-pointer flex items-center gap-1"
                        htmlFor={`type-${type.id}`}
                      >
                        {type.icon}
                        {type.name}
                      </label>
                    </div>
                  ))}
                </div>
              </div>

              <div className="md:w-1/2">
                <h4 className="text-sm font-semibold mb-3">Tags</h4>
                <div className="flex flex-wrap gap-2">
                  {tags.map((tag) => (
                    <button
                      className={`px-3 py-1.5 rounded-full text-sm flex items-center gap-1 transition-colors ${
                        selectedTags.includes(tag.id)
                          ? "bg-opacity-20 border-2"
                          : "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
                      }`}
                      key={tag.id}
                      onClick={() => toggleTag(tag.id)}
                      style={{
                        backgroundColor: selectedTags.includes(tag.id)
                          ? `${tag.color}20`
                          : "",
                        borderColor: selectedTags.includes(tag.id)
                          ? tag.color
                          : "transparent",
                        color: selectedTags.includes(tag.id) ? tag.color : "",
                      }}
                    >
                      <div
                        className="w-2 h-2 rounded-full"
                        style={{ backgroundColor: tag.color }}
                      />
                      {tag.name}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            <div className="flex justify-end gap-2 mt-4">
              <Button onClick={clearAllFilters} size="sm" variant="outline">
                Reset Filters
              </Button>
              <Button onClick={() => setIsFilterExpanded(false)} size="sm">
                Apply Filters
              </Button>
            </div>
          </div>
        )}
      </div>

      {/* Active filters display */}
      {(filterType || searchInput || activeFiltersCount > 0) && (
        <div className="mb-6">
          <div className="flex items-center gap-2">
            <span className="text-sm text-gray-500 dark:text-gray-400">
              Active filters:
            </span>
            <div className="flex flex-wrap gap-2">
              {filterType && (
                <span className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm flex items-center gap-1">
                  {filterType.charAt(0).toUpperCase() + filterType.slice(1)}
                  <button
                    className="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100"
                    onClick={() => handleFilterChange(null)}
                  >
                    ×
                  </button>
                </span>
              )}
              {searchInput && (
                <span className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm flex items-center gap-1">
                  Search: {searchInput}
                  <button
                    className="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100"
                    onClick={() =>
                      handleSearch({
                        target: { value: "" },
                      } as React.ChangeEvent<HTMLInputElement>)
                    }
                  >
                    ×
                  </button>
                </span>
              )}

              {/* Show selected file types as filter pills */}
              {selectedFileTypes.map((typeId) => {
                const type = fileTypes.find((t) => t.id === typeId);
                return type ? (
                  <span
                    className="bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 px-3 py-1 rounded-full text-sm flex items-center gap-1"
                    key={`filetype-${typeId}`}
                  >
                    {type.icon}
                    {type.name}
                    <button
                      className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100"
                      onClick={() => toggleFileType(typeId)}
                    >
                      ×
                    </button>
                  </span>
                ) : null;
              })}

              {/* Show selected tags as filter pills */}
              {selectedTags.map((tagId) => {
                const tag = tags.find((t) => t.id === tagId);
                return tag ? (
                  <span
                    className="px-3 py-1 rounded-full text-sm flex items-center gap-1"
                    key={`tag-${tagId}`}
                    style={{
                      backgroundColor: `${tag.color}20`,
                      color: tag.color,
                      borderWidth: "1px",
                      borderStyle: "solid",
                      borderColor: tag.color,
                    }}
                  >
                    <div
                      className="w-2 h-2 rounded-full"
                      style={{ backgroundColor: tag.color }}
                    />
                    {tag.name}
                    <button
                      className="ml-1 hover:opacity-80"
                      onClick={() => toggleTag(tagId)}
                      style={{ color: tag.color }}
                    >
                      ×
                    </button>
                  </span>
                ) : null;
              })}
            </div>

            {(filterType || searchInput || activeFiltersCount > 0) && (
              <button
                className="ml-2 text-sm text-blue-600 dark:text-blue-400 hover:underline"
                onClick={clearAllFilters}
              >
                Clear all
              </button>
            )}
          </div>
        </div>
      )}
    </>
  );
};

export default FilterPanel;
