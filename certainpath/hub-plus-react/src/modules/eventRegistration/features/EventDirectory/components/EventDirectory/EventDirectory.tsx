import React, { useState, useEffect } from "react";
import { Search } from "lucide-react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import InfiniteScroll from "react-infinite-scroll-component";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { fetchEventSearchResultsAction } from "../../slices/eventDirectorySlice";
import {
  FetchEventSearchResultsRequest,
  SearchResultEvent,
} from "../../api/fetchEventSearchResults/types";
import { EventDirectoryCard } from "../EventDirectoryCard/EventDirectoryCard";
import { EventDirectoryLoadingSkeleton } from "../EventDirectoryLoadingSkeleton/EventDirectoryLoadingSkeleton";
import { EventDirectoryCardsSkeleton } from "../EventDirectoryCardsSkeleton/EventDirectoryCardsSkeleton";
import EventDirectoryFilterDrawer from "../EventDirectoryFilterDrawer/EventDirectoryFilterDrawer";
import CurrentFilterChips from "../CurrentFilterChips/CurrentFilterChips";
import EventDirectoryActions from "../EventDirectoryActions/EventDirectoryActions";

export default function EventDirectory() {
  const dispatch = useAppDispatch();

  const {
    searchData: events,
    searchTotalCount: totalCount,
    fetchSearchLoading,
    fetchSearchError,
    searchFilters,
  } = useAppSelector((state: RootState) => state.eventDirectory);

  const { eventTypes, categories, trades, employeeRoles } = searchFilters;

  const [isInitialLoading, setIsInitialLoading] = useState(true);
  const [isPaginationLoading, setIsPaginationLoading] = useState(false);
  const [page, setPage] = useState(1);
  const hasMore = events.length < totalCount;
  const [searchInput, setSearchInput] = useState("");
  const debouncedSearch = useDebouncedValue(searchInput, 500);
  const [showOnlyFavorites, setShowOnlyFavorites] = useState(false);
  const [selectedEventType, setSelectedEventType] = useState<number | null>(
    null,
  );
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null);
  const [selectedTrade, setSelectedTrade] = useState<number | null>(null);
  const [selectedEmployeeRole, setSelectedEmployeeRole] = useState<
    number | null
  >(null);
  const [onlyPastEvents, setOnlyPastEvents] = useState(false);
  const [startDate, setStartDate] = useState<string | null>(null);
  const [endDate, setEndDate] = useState<string | null>(null);
  const [isDrawerOpen, setIsDrawerOpen] = useState(false);

  useEffect(() => {
    loadInitialData();
  }, []);

  useEffect(() => {
    if (!isInitialLoading) {
      resetAndFetch();
    }
  }, [
    debouncedSearch,
    showOnlyFavorites,
    selectedEventType,
    selectedCategory,
    selectedTrade,
    selectedEmployeeRole,
    onlyPastEvents,
    startDate,
    endDate,
  ]);

  async function loadInitialData() {
    await resetAndFetch();
    setIsInitialLoading(false);
  }

  async function resetAndFetch() {
    setPage(1);
    await dispatchFetchPage(1);
  }

  async function dispatchFetchPage(pageNumber: number) {
    const params: FetchEventSearchResultsRequest = {
      page: pageNumber,
      pageSize: 8,
    };

    if (debouncedSearch.trim()) {
      params.searchTerm = debouncedSearch.trim();
    }
    if (showOnlyFavorites) {
      params.showFavorites = true;
    }
    if (selectedEventType) {
      params.eventTypeIds = [selectedEventType];
    }
    if (selectedCategory) {
      params.categoryIds = [selectedCategory];
    }
    if (selectedTrade) {
      params.tradeIds = [selectedTrade];
    }
    if (selectedEmployeeRole) {
      params.employeeRoleIds = [selectedEmployeeRole];
    }
    if (onlyPastEvents === true) {
      params.onlyPastEvents = true;
    }
    if (startDate) {
      params.startDate = startDate;
    }
    if (endDate) {
      params.endDate = endDate;
    }

    await dispatch(fetchEventSearchResultsAction(params));
  }

  const fetchMore = async () => {
    if (isPaginationLoading) return;

    setIsPaginationLoading(true);
    const nextPage = page + 1;
    setPage(nextPage);
    await dispatchFetchPage(nextPage);
    setIsPaginationLoading(false);
  };

  function clearAllFilters() {
    setSearchInput("");
    setShowOnlyFavorites(false);
    setSelectedEventType(null);
    setSelectedCategory(null);
    setSelectedTrade(null);
    setSelectedEmployeeRole(null);
    setOnlyPastEvents(false);
    setStartDate(null);
    setEndDate(null);
  }

  function handleRemoveSearchInput() {
    setSearchInput("");
  }
  function handleRemoveFavorites() {
    setShowOnlyFavorites(false);
  }
  function handleRemoveEventType() {
    setSelectedEventType(null);
  }
  function handleRemoveCategory() {
    setSelectedCategory(null);
  }
  function handleRemoveTrade() {
    setSelectedTrade(null);
  }
  function handleRemoveEmployeeRole() {
    setSelectedEmployeeRole(null);
  }
  function handleRemovePastEvents() {
    setOnlyPastEvents(false);
  }
  function handleRemoveStartDate() {
    setStartDate(null);
  }
  function handleRemoveEndDate() {
    setEndDate(null);
  }

  const activeFilterCount = [
    selectedEventType,
    selectedCategory,
    selectedTrade,
    selectedEmployeeRole,
    onlyPastEvents ? true : null,
    startDate,
    endDate,
  ].filter(Boolean).length;

  const hasActiveSearch = Boolean(searchInput.trim());
  const hasActiveFavorites = showOnlyFavorites;

  const actions = (
    <EventDirectoryActions
      activeFilterCount={activeFilterCount}
      hasActiveFavorites={hasActiveFavorites}
      hasActiveSearch={hasActiveSearch}
      onOpenDrawer={() => setIsDrawerOpen(true)}
    />
  );

  if (isInitialLoading) {
    return <EventDirectoryLoadingSkeleton count={8} />;
  }

  return (
    <MainPageWrapper
      actions={actions}
      error={fetchSearchError || undefined}
      subtitle="Explore and register for events near you or online"
      title="Event Directory"
    >
      <div className="relative mb-6">
        <input
          className="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-800 px-4 py-2 pl-10"
          onChange={(e) => setSearchInput(e.target.value)}
          placeholder="Search events..."
          type="text"
          value={searchInput}
        />
        <Search
          className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
          size={20}
        />
      </div>

      <CurrentFilterChips
        categories={categories}
        employeeRoles={employeeRoles}
        endDate={endDate}
        eventTypes={eventTypes}
        onClearAll={clearAllFilters}
        onRemoveCategory={handleRemoveCategory}
        onRemoveEmployeeRole={handleRemoveEmployeeRole}
        onRemoveEndDate={handleRemoveEndDate}
        onRemoveEventType={handleRemoveEventType}
        onRemoveFavorites={handleRemoveFavorites}
        onRemovePastEvents={handleRemovePastEvents}
        onRemoveSearchInput={handleRemoveSearchInput}
        onRemoveStartDate={handleRemoveStartDate}
        onRemoveTrade={handleRemoveTrade}
        onlyPastEvents={onlyPastEvents}
        searchInput={searchInput}
        selectedCategory={selectedCategory}
        selectedEmployeeRole={selectedEmployeeRole}
        selectedEventType={selectedEventType}
        selectedTrade={selectedTrade}
        showOnlyFavorites={showOnlyFavorites}
        startDate={startDate}
        trades={trades}
      />

      <EventDirectoryFilterDrawer
        clearAllFilters={clearAllFilters}
        endDate={endDate}
        isOpen={isDrawerOpen}
        onCategorySelect={setSelectedCategory}
        onClose={() => setIsDrawerOpen(false)}
        onEmployeeRoleSelect={setSelectedEmployeeRole}
        onEndDateChange={setEndDate}
        onEventTypeSelect={setSelectedEventType}
        onSearchInputChange={setSearchInput}
        onStartDateChange={setStartDate}
        onToggleFavorites={setShowOnlyFavorites}
        onToggleOnlyPastEvents={setOnlyPastEvents}
        onTradeSelect={setSelectedTrade}
        onlyPastEvents={onlyPastEvents}
        searchInput={searchInput}
        selectedCategory={selectedCategory}
        selectedEmployeeRole={selectedEmployeeRole}
        selectedEventType={selectedEventType}
        selectedTrade={selectedTrade}
        showOnlyFavorites={showOnlyFavorites}
        startDate={startDate}
      />

      {fetchSearchLoading && page === 1 ? (
        <EventDirectoryCardsSkeleton count={8} />
      ) : (
        <InfiniteScroll
          dataLength={events.length}
          endMessage={
            events.length > 0 ? (
              <div className="text-center py-4">
                <p className="text-gray-500 dark:text-gray-400">
                  No more events to load
                </p>
              </div>
            ) : null
          }
          hasMore={hasMore}
          loader={
            <div className="text-center py-4">
              <p className="text-gray-500 dark:text-gray-400">
                Loading more...
              </p>
            </div>
          }
          next={fetchMore}
          style={{ overflow: "visible" }}
        >
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {events.length > 0 ? (
              events.map((evt: SearchResultEvent) => (
                <div className="fade-in" key={evt.uuid}>
                  <EventDirectoryCard event={evt} />
                </div>
              ))
            ) : (
              <div className="col-span-full text-center py-12">
                <p className="text-gray-500 dark:text-gray-400 text-lg">
                  No events found matching your criteria.
                </p>
                <button
                  className="mt-4 text-blue-600 dark:text-blue-400 hover:underline"
                  onClick={clearAllFilters}
                >
                  Clear all filters
                </button>
              </div>
            )}
          </div>
        </InfiniteScroll>
      )}
    </MainPageWrapper>
  );
}
