import React from "react";
import { Filter } from "lucide-react";

interface EventDirectoryActionsProps {
  activeFilterCount: number;
  hasActiveSearch: boolean;
  hasActiveFavorites: boolean;
  onOpenDrawer: () => void;
}

export default function EventDirectoryActions({
  activeFilterCount,
  hasActiveSearch,
  hasActiveFavorites,
  onOpenDrawer,
}: EventDirectoryActionsProps) {
  const totalActiveCount =
    activeFilterCount +
    (hasActiveSearch ? 1 : 0) +
    (hasActiveFavorites ? 1 : 0);

  return (
    <div className="flex items-center space-x-4">
      <button
        className="inline-flex items-center space-x-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700"
        onClick={onOpenDrawer}
        type="button"
      >
        <Filter className="h-4 w-4" />
        <span>Filters</span>
        {totalActiveCount > 0 && (
          <span className="ml-2 inline-flex items-center justify-center rounded-full bg-gray-200 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-200">
            {totalActiveCount}
          </span>
        )}
      </button>
    </div>
  );
}
