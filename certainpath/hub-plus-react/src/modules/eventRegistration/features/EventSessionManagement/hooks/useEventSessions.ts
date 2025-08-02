import { useState, useEffect, useCallback } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { fetchEventSessionsAction } from "@/modules/eventRegistration/features/EventSessionManagement/slices/eventSessionListSlice";

type EventSessionFilters = {
  searchTerm: string;
};

export function useEventSessions(eventUuid?: string) {
  const dispatch = useAppDispatch();

  const sessions = useAppSelector(
    (state: RootState) => state.eventSessionList.sessions,
  );
  const totalCount = useAppSelector(
    (state: RootState) => state.eventSessionList.totalCount,
  );
  const eventName = useAppSelector(
    (state: RootState) => state.eventSessionList.eventName,
  );
  const loading = useAppSelector(
    (state: RootState) => state.eventSessionList.fetchLoading,
  );
  const error = useAppSelector(
    (state: RootState) => state.eventSessionList.fetchError,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<EventSessionFilters>({
    searchTerm: "",
  });

  const refetchSessions = useCallback(() => {
    if (!eventUuid) return;

    const sortBy = sorting.length ? sorting[0].id : undefined;
    const sortOrder = sorting.length
      ? sorting[0].desc
        ? "desc"
        : "asc"
      : undefined;

    dispatch(
      fetchEventSessionsAction({
        page: pagination.pageIndex + 1,
        pageSize: pagination.pageSize,
        sortBy,
        sortOrder,
        eventUuid: eventUuid,
      }),
    );
  }, [eventUuid, dispatch, sorting, pagination]);

  useEffect(() => {
    refetchSessions();
  }, [refetchSessions]);

  const handlePaginationChange: OnChangeFn<PaginationState> = (
    updaterOrValue,
  ) => {
    setPagination((old) =>
      typeof updaterOrValue === "function"
        ? updaterOrValue(old)
        : updaterOrValue,
    );
  };

  const handleSortingChange: OnChangeFn<SortingState> = (updaterOrValue) => {
    setSorting((old) =>
      typeof updaterOrValue === "function"
        ? updaterOrValue(old)
        : updaterOrValue,
    );
  };

  const handleFilterChange = (filterKey: string, value: string | string[]) => {
    setFilters((prev) => ({
      ...prev,
      [filterKey]: value,
    }));
  };

  return {
    sessions,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    refetchSessions,
    eventName,
  };
}
