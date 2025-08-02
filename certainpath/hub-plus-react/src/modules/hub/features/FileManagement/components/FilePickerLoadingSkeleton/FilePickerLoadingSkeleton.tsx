import React from "react";
import "./FilePickerLoadingSkeleton.css";

interface FileManagerLoadingSkeletonProps {
  count: number;
  viewMode?: "grid" | "list";
}

const FilePickerLoadingSkeleton: React.FC<FileManagerLoadingSkeletonProps> = ({
  count,
  viewMode = "grid",
}) => {
  return (
    <div className="space-y-6 animate-pulse">
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
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
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
  );
};

export default FilePickerLoadingSkeleton;
