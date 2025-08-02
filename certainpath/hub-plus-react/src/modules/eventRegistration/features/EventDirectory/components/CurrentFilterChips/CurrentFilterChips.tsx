import React from "react";

interface Props {
  searchInput: string;
  onRemoveSearchInput: () => void;
  showOnlyFavorites: boolean;
  onRemoveFavorites: () => void;
  selectedEventType: number | null;
  onRemoveEventType: () => void;
  selectedCategory: number | null;
  onRemoveCategory: () => void;
  selectedTrade: number | null;
  onRemoveTrade: () => void;
  selectedEmployeeRole: number | null;
  onRemoveEmployeeRole: () => void;
  eventTypes: { id: number; name: string }[];
  categories: { id: number; name: string }[];
  trades: { id: number; name: string }[];
  employeeRoles: { id: number; name: string }[];
  onClearAll: () => void;
  onlyPastEvents: boolean;
  onRemovePastEvents: () => void;
  startDate: string | null;
  onRemoveStartDate: () => void;
  endDate: string | null;
  onRemoveEndDate: () => void;
}

export default function CurrentFilterChips({
  searchInput,
  onRemoveSearchInput,
  showOnlyFavorites,
  onRemoveFavorites,
  selectedEventType,
  onRemoveEventType,
  selectedCategory,
  onRemoveCategory,
  selectedTrade,
  onRemoveTrade,
  selectedEmployeeRole,
  onRemoveEmployeeRole,
  eventTypes,
  categories,
  trades,
  employeeRoles,
  onClearAll,
  onlyPastEvents,
  onRemovePastEvents,
  startDate,
  onRemoveStartDate,
  endDate,
  onRemoveEndDate,
}: Props) {
  const getEventTypeName = () =>
    eventTypes.find((et) => et.id === selectedEventType)?.name ||
    "Unknown type";
  const getCategoryName = () =>
    categories.find((c) => c.id === selectedCategory)?.name ||
    "Unknown category";
  const getTradeName = () =>
    trades.find((t) => t.id === selectedTrade)?.name || "Unknown trade";
  const getEmployeeRoleName = () =>
    employeeRoles.find((r) => r.id === selectedEmployeeRole)?.name ||
    "Unknown role";

  const hasAnyActiveFilter =
    !!searchInput ||
    showOnlyFavorites ||
    selectedEventType !== null ||
    selectedCategory !== null ||
    selectedTrade !== null ||
    selectedEmployeeRole !== null ||
    onlyPastEvents ||
    startDate ||
    endDate;

  if (!hasAnyActiveFilter) {
    return null;
  }

  return (
    <div className="mb-6 flex flex-wrap items-center gap-2 text-sm">
      <span className="text-gray-700 dark:text-gray-300">Active filters:</span>

      {searchInput && (
        <span className="flex items-center gap-1 rounded-full bg-gray-200 px-3 py-1 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
          {searchInput}
          <button
            className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={onRemoveSearchInput}
          >
            ×
          </button>
        </span>
      )}

      {showOnlyFavorites && (
        <span className="flex items-center gap-1 rounded-full bg-gray-200 px-3 py-1 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
          Favorites
          <button
            className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={onRemoveFavorites}
          >
            ×
          </button>
        </span>
      )}

      {selectedEventType !== null && (
        <span className="flex items-center gap-1 rounded-full bg-gray-200 px-3 py-1 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
          {getEventTypeName()}
          <button
            className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={onRemoveEventType}
          >
            ×
          </button>
        </span>
      )}

      {selectedCategory !== null && (
        <span className="flex items-center gap-1 rounded-full bg-gray-200 px-3 py-1 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
          {getCategoryName()}
          <button
            className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={onRemoveCategory}
          >
            ×
          </button>
        </span>
      )}

      {selectedTrade !== null && (
        <span className="flex items-center gap-1 rounded-full bg-gray-200 px-3 py-1 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
          {getTradeName()}
          <button
            className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={onRemoveTrade}
          >
            ×
          </button>
        </span>
      )}

      {selectedEmployeeRole !== null && (
        <span className="flex items-center gap-1 rounded-full bg-gray-200 px-3 py-1 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
          {getEmployeeRoleName()}
          <button
            className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={onRemoveEmployeeRole}
          >
            ×
          </button>
        </span>
      )}

      {onlyPastEvents && (
        <span className="flex items-center gap-1 rounded-full bg-gray-200 px-3 py-1 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
          Past Events
          <button
            className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={onRemovePastEvents}
          >
            ×
          </button>
        </span>
      )}

      {startDate && (
        <span className="flex items-center gap-1 rounded-full bg-gray-200 px-3 py-1 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
          Start: {startDate}
          <button
            className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={onRemoveStartDate}
          >
            ×
          </button>
        </span>
      )}

      {endDate && (
        <span className="flex items-center gap-1 rounded-full bg-gray-200 px-3 py-1 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
          End: {endDate}
          <button
            className="ml-1 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
            onClick={onRemoveEndDate}
          >
            ×
          </button>
        </span>
      )}

      <button
        className="ml-2 text-blue-600 dark:text-blue-400 underline"
        onClick={onClearAll}
      >
        Clear all
      </button>
    </div>
  );
}
