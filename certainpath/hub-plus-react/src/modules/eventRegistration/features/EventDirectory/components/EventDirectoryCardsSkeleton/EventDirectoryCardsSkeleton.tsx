import React from "react";

export function EventDirectoryCardsSkeleton({ count = 4 }: { count?: number }) {
  return (
    <div className="animate-pulse grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      {Array.from({ length: count }).map((_, i) => (
        <div
          className="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden"
          key={i}
        >
          <div className="h-48 bg-gray-200 dark:bg-gray-700"></div>
          <div className="p-4">
            <div className="h-6 bg-gray-200 dark:bg-gray-700 rounded mb-3 w-3/4"></div>
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded mb-2 w-1/2"></div>
          </div>
        </div>
      ))}
    </div>
  );
}
