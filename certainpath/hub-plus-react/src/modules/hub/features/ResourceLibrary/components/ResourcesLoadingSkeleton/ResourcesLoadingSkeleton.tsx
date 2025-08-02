import React from "react";

export function ResourcesLoadingSkeleton({ count = 4 }: { count?: number }) {
  return (
    <div className="container mx-auto px-4 py-8">
      <div className="animate-pulse mb-8">
        <div className="h-10 bg-gray-200 dark:bg-gray-700 rounded mb-8"></div>
        <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded mb-4 w-1/4"></div>
        <div className="flex flex-wrap gap-2 mb-8">
          {Array.from({ length: 5 }).map((_, i) => (
            <div
              className="h-8 bg-gray-200 dark:bg-gray-700 rounded-full w-24"
              key={i}
            ></div>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
    </div>
  );
}
