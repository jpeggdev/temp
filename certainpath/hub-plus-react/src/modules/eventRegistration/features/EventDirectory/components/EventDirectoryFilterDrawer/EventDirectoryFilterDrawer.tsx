import React, { Fragment } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { Search, X, Star, Clock } from "lucide-react";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import { useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";

interface EventDirectoryFilterDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  searchInput: string;
  onSearchInputChange: (value: string) => void;
  showOnlyFavorites: boolean;
  onToggleFavorites: (checked: boolean) => void;
  selectedEventType: number | null;
  onEventTypeSelect: (typeId: number | null) => void;
  selectedCategory: number | null;
  onCategorySelect: (catId: number | null) => void;
  selectedTrade: number | null;
  onTradeSelect: (tradeId: number | null) => void;
  selectedEmployeeRole: number | null;
  onEmployeeRoleSelect: (roleId: number | null) => void;
  clearAllFilters: () => void;
  onlyPastEvents: boolean;
  onToggleOnlyPastEvents: (checked: boolean) => void;
  startDate: string | null;
  onStartDateChange: (value: string) => void;
  endDate: string | null;
  onEndDateChange: (value: string) => void;
}

export default function EventDirectoryFilterDrawer({
  isOpen,
  onClose,
  searchInput,
  onSearchInputChange,
  showOnlyFavorites,
  onToggleFavorites,
  selectedEventType,
  onEventTypeSelect,
  selectedCategory,
  onCategorySelect,
  selectedTrade,
  onTradeSelect,
  selectedEmployeeRole,
  onEmployeeRoleSelect,
  clearAllFilters,
  onlyPastEvents,
  onToggleOnlyPastEvents,
  startDate,
  onStartDateChange,
  endDate,
  onEndDateChange,
}: EventDirectoryFilterDrawerProps) {
  const { eventTypes, categories, trades, employeeRoles } = useAppSelector(
    (state: RootState) => state.eventDirectory.searchFilters,
  );

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog as="div" className="relative z-40" onClose={onClose}>
        <Transition.Child
          as={Fragment}
          enter="transition-opacity ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="transition-opacity ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-black bg-opacity-30" />
        </Transition.Child>

        <div className="fixed inset-0 overflow-hidden">
          <div className="absolute inset-0 overflow-hidden">
            <div className="pointer-events-none fixed inset-y-0 right-0 flex max-w-full">
              <Transition.Child
                as={Fragment}
                enter="transform transition ease-in-out duration-300"
                enterFrom="translate-x-full"
                enterTo="translate-x-0"
                leave="transform transition ease-in-out duration-300"
                leaveFrom="translate-x-0"
                leaveTo="translate-x-full"
              >
                <Dialog.Panel className="pointer-events-auto w-screen max-w-lg bg-white dark:bg-gray-900 flex flex-col">
                  {/* Header */}
                  <div className="flex items-center justify-between bg-primary px-4 py-4">
                    <Dialog.Title className="text-lg font-medium text-white">
                      Filters
                    </Dialog.Title>
                    <button
                      className="text-white"
                      onClick={onClose}
                      type="button"
                    >
                      <X className="h-6 w-6" />
                    </button>
                  </div>

                  <div className="flex-1 overflow-y-auto px-4 py-4 sm:px-6 dark:text-gray-100">
                    <div className="relative mb-6">
                      <input
                        className="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 pl-10"
                        onChange={(e) => onSearchInputChange(e.target.value)}
                        placeholder="Search events..."
                        type="text"
                        value={searchInput}
                      />
                      <Search
                        className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                        size={20}
                      />
                    </div>

                    <div className="flex items-center space-x-2 mb-6">
                      <Switch
                        checked={showOnlyFavorites}
                        id="show-favorites"
                        onCheckedChange={onToggleFavorites}
                      />
                      <Label
                        className="flex items-center gap-2 cursor-pointer text-sm font-medium"
                        htmlFor="show-favorites"
                      >
                        <Star
                          className={`h-4 w-4 ${
                            showOnlyFavorites
                              ? "text-yellow-500 fill-yellow-500"
                              : ""
                          }`}
                        />
                        Favorites only
                      </Label>
                    </div>

                    <div className="flex items-center space-x-2 mb-6">
                      <Switch
                        checked={onlyPastEvents}
                        id="show-past-events"
                        onCheckedChange={onToggleOnlyPastEvents}
                      />
                      <Label
                        className="flex items-center gap-2 cursor-pointer text-sm font-medium"
                        htmlFor="show-past-events"
                      >
                        <Clock
                          className={`h-4 w-4 ${
                            onlyPastEvents
                              ? "text-gray-600 dark:text-gray-300"
                              : "text-gray-400"
                          }`}
                        />
                        Show Past Events
                      </Label>
                    </div>

                    <div className="mb-8">
                      <p className="text-xs text-gray-500 dark:text-gray-400 mb-2 leading-tight">
                        *Below fields filter events by session{" "}
                        <strong>start date</strong>. If an event’s session
                        starts within this range, it will be shown.
                      </p>
                      <div className="flex flex-col gap-4">
                        <div>
                          <Label
                            className="block mb-1 font-medium text-sm text-gray-700 dark:text-gray-300"
                            htmlFor="start-date-input"
                          >
                            Start Date
                          </Label>
                          <input
                            className="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2"
                            id="start-date-input"
                            onChange={(e) => onStartDateChange(e.target.value)}
                            type="date"
                            value={startDate || ""}
                          />
                        </div>

                        <div>
                          <Label
                            className="block mb-1 font-medium text-sm text-gray-700 dark:text-gray-300"
                            htmlFor="end-date-input"
                          >
                            End Date
                          </Label>
                          <input
                            className="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2"
                            id="end-date-input"
                            onChange={(e) => onEndDateChange(e.target.value)}
                            type="date"
                            value={endDate || ""}
                          />
                        </div>
                      </div>
                    </div>

                    <div className="mb-8">
                      <h2 className="text-lg font-semibold mb-4">
                        Event Types
                      </h2>
                      <div className="flex flex-wrap gap-2">
                        {eventTypes.map((type) => (
                          <button
                            className={`px-4 py-2 rounded-lg ${
                              selectedEventType === type.id
                                ? "bg-primary text-white"
                                : "bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300"
                            } text-sm flex items-center gap-2`}
                            key={type.id}
                            onClick={() =>
                              onEventTypeSelect(
                                selectedEventType === type.id ? null : type.id,
                              )
                            }
                          >
                            <span>{type.name}</span>
                            <span
                              className={`rounded-full px-2 py-0.5 text-xs ${
                                selectedEventType === type.id
                                  ? "bg-white text-primary"
                                  : "bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                              }`}
                            >
                              {type.eventCount}
                            </span>
                          </button>
                        ))}
                      </div>
                    </div>

                    <div className="mb-8">
                      <h2 className="text-lg font-semibold mb-4">Categories</h2>
                      <div className="flex flex-wrap gap-2">
                        {categories.map((cat) => (
                          <button
                            className={`px-4 py-2 rounded-lg ${
                              selectedCategory === cat.id
                                ? "bg-primary text-white"
                                : "bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300"
                            } text-sm flex items-center gap-2`}
                            key={cat.id}
                            onClick={() =>
                              onCategorySelect(
                                selectedCategory === cat.id ? null : cat.id,
                              )
                            }
                          >
                            <span>{cat.name}</span>
                            <span
                              className={`rounded-full px-2 py-0.5 text-xs ${
                                selectedCategory === cat.id
                                  ? "bg-white text-primary"
                                  : "bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                              }`}
                            >
                              {cat.eventCount}
                            </span>
                          </button>
                        ))}
                      </div>
                    </div>

                    <div className="mb-8">
                      <h2 className="text-lg font-semibold mb-4">Trades</h2>
                      <div className="flex flex-wrap gap-2">
                        {trades.map((trade) => (
                          <button
                            className={`px-4 py-2 rounded-lg ${
                              selectedTrade === trade.id
                                ? "bg-primary text-white"
                                : "bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300"
                            } text-sm flex items-center gap-2`}
                            key={trade.id}
                            onClick={() =>
                              onTradeSelect(
                                selectedTrade === trade.id ? null : trade.id,
                              )
                            }
                          >
                            <span>{trade.name}</span>
                            <span
                              className={`rounded-full px-2 py-0.5 text-xs ${
                                selectedTrade === trade.id
                                  ? "bg-white text-primary"
                                  : "bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                              }`}
                            >
                              {trade.eventCount}
                            </span>
                          </button>
                        ))}
                      </div>
                    </div>

                    <div className="mb-8">
                      <h2 className="text-lg font-semibold mb-4">
                        Employee Roles
                      </h2>
                      <div className="flex flex-wrap gap-2">
                        {employeeRoles.map((role) => (
                          <button
                            className={`px-4 py-2 rounded-lg ${
                              selectedEmployeeRole === role.id
                                ? "bg-primary text-white"
                                : "bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300"
                            } text-sm flex items-center gap-2`}
                            key={role.id}
                            onClick={() =>
                              onEmployeeRoleSelect(
                                selectedEmployeeRole === role.id
                                  ? null
                                  : role.id,
                              )
                            }
                          >
                            <span>{role.name}</span>
                            <span
                              className={`rounded-full px-2 py-0.5 text-xs ${
                                selectedEmployeeRole === role.id
                                  ? "bg-white text-primary"
                                  : "bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                              }`}
                            >
                              {role.eventCount}
                            </span>
                          </button>
                        ))}
                      </div>
                    </div>

                    <button
                      className="mt-6 w-full rounded-md bg-gray-200 dark:bg-gray-800 py-2 text-sm font-semibold text-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-700"
                      onClick={clearAllFilters}
                    >
                      Clear All Filters
                    </button>
                  </div>
                </Dialog.Panel>
              </Transition.Child>
            </div>
          </div>
        </div>
      </Dialog>
    </Transition.Root>
  );
}
