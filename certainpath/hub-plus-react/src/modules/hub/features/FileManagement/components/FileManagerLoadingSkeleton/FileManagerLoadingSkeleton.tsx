import React from "react";
import "./FileManagerLoadingSkeleton.css";

interface FileManagerLoadingSkeletonProps {
  count: number;
  viewMode?: "grid" | "list";
}

const FileManagerLoadingSkeleton: React.FC<FileManagerLoadingSkeletonProps> = ({
  count,
  viewMode = "grid",
}) => {
  return (
    <div className="flex flex-col lg:flex-row gap-6">
      {/* Sidebar skeleton - only visible on desktop */}
      <aside className="hidden lg:block w-[280px] animate-pulse">
        <div className="sticky top-20 space-y-6 p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
          {/* Sidebar header */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <div className="w-5 h-5 bg-gray-200 dark:bg-gray-700 rounded"></div>
              <div className="w-16 h-6 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
          </div>

          {/* Divider */}
          <div className="h-px bg-gray-200 dark:bg-gray-700"></div>

          {/* File Types section */}
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
              <div className="w-20 h-5 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>

            {/* File type items */}
            <div className="space-y-2 ml-1">
              {Array(5)
                .fill(0)
                .map((_, index) => (
                  <div
                    className="flex items-center gap-3"
                    key={`type-${index}`}
                  >
                    <div className="w-4 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div className="flex-1 flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <div className="w-5 h-5 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div className="w-16 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                      </div>
                      <div className="w-6 h-4 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    </div>
                  </div>
                ))}
            </div>
          </div>

          {/* Divider */}
          <div className="h-px bg-gray-200 dark:bg-gray-700"></div>

          {/* Tags section */}
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
              <div className="w-12 h-5 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>

            {/* Tag items */}
            <div className="space-y-2 ml-1">
              {Array(4)
                .fill(0)
                .map((_, index) => (
                  <div className="flex items-center gap-3" key={`tag-${index}`}>
                    <div className="w-4 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div className="flex-1 flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <div className="w-3 h-3 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                        <div className="w-20 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                      </div>
                      <div className="w-6 h-4 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    </div>
                  </div>
                ))}
            </div>
          </div>
        </div>
      </aside>

      {/* Main content area */}
      <div className="flex-1 min-w-0 space-y-6 animate-pulse">
        {/* Actions row skeleton */}
        <div className="flex flex-col md:flex-row gap-4 md:items-center justify-between mb-6">
          {/* Search bar skeleton */}
          <div className="relative flex-1">
            <div className="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-200 dark:bg-gray-700"></div>
          </div>

          {/* Action buttons skeleton */}
          <div className="flex items-center gap-3">
            {/* Filter button for mobile */}
            <div className="lg:hidden w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>

            {/* Upload button */}
            <div className="w-28 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>

            {/* New folder button - hidden on mobile */}
            <div className="hidden md:block w-28 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>

            {/* Select button - hidden on mobile */}
            <div className="hidden md:block w-24 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>

            {/* View toggle */}
            <div className="flex items-center border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
              <div className="w-10 h-10 bg-gray-200 dark:bg-gray-700"></div>
              <div className="w-10 h-10 bg-gray-200 dark:bg-gray-700"></div>
            </div>
          </div>
        </div>

        {/* Breadcrumbs skeleton */}
        <div className="flex items-center gap-2 mb-6">
          <div className="w-20 h-6 bg-gray-200 dark:bg-gray-700 rounded"></div>
          <div className="w-28 h-6 bg-gray-200 dark:bg-gray-700 rounded"></div>
        </div>

        {/* Sort controls for list view */}
        {viewMode === "list" && (
          <div className="file-list-header mb-2 border-b border-gray-200 dark:border-gray-700 pb-2 hidden md:flex">
            <div className="flex-1 flex items-center gap-2">
              <div className="w-16 h-5 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
            <div className="w-24 flex items-center gap-1">
              <div className="w-12 h-5 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
            <div className="w-32 flex items-center gap-1">
              <div className="w-20 h-5 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
            <div className="w-24">
              <div className="w-12 h-5 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
            <div className="w-8"></div>
          </div>
        )}

        {/* Files/folders grid or list */}
        {viewMode === "grid" ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {Array(count)
              .fill(0)
              .map((_, index) => (
                <div
                  className="loading-skeleton-item p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800"
                  key={index}
                >
                  <div className="flex flex-col items-center">
                    <div className="skeleton-icon mb-2 w-12 h-12 rounded-md bg-gray-200 dark:bg-gray-700" />
                    <div className="skeleton-name w-3/4 h-4 bg-gray-200 dark:bg-gray-700 rounded" />
                    <div className="skeleton-meta w-1/2 h-3 mt-2 bg-gray-200 dark:bg-gray-700 rounded" />
                  </div>
                </div>
              ))}
          </div>
        ) : (
          <div className="flex flex-col gap-1">
            {Array(count)
              .fill(0)
              .map((_, index) => (
                <div
                  className="loading-skeleton-item px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center"
                  key={index}
                >
                  <div className="flex-1 flex items-center gap-3">
                    <div className="skeleton-icon w-5 h-5 rounded-md bg-gray-200 dark:bg-gray-700" />
                    <div className="skeleton-name w-1/3 h-4 bg-gray-200 dark:bg-gray-700 rounded" />
                  </div>
                  <div className="w-24 hidden md:block">
                    <div className="skeleton-type w-16 h-4 bg-gray-200 dark:bg-gray-700 rounded" />
                  </div>
                  <div className="w-32 hidden md:block">
                    <div className="skeleton-date w-24 h-4 bg-gray-200 dark:bg-gray-700 rounded" />
                  </div>
                  <div className="w-24 hidden md:block">
                    <div className="skeleton-size w-16 h-4 bg-gray-200 dark:bg-gray-700 rounded" />
                  </div>
                  <div className="w-8">
                    <div className="skeleton-actions w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700" />
                  </div>
                </div>
              ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default FileManagerLoadingSkeleton;
