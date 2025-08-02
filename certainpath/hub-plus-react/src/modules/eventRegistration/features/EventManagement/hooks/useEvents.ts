import { useCallback, useEffect, useState } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { fetchEventsAction } from "../slices/eventListSlice";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";

type EventFilters = {
  searchTerm: string;
  tradeIds: string[];
  eventTypeIds: string[];
  employeeRoleIds: string[];
  categoryIds: string[];
  tagIds: string[];
};

export const useEvents = () => {
  const dispatch = useAppDispatch();

  const events = useAppSelector((state: RootState) => state.eventList.events);
  const totalCount = useAppSelector(
    (state: RootState) => state.eventList.totalCount,
  );
  const loading = useAppSelector(
    (state: RootState) => state.eventList.fetchLoading,
  );
  const error = useAppSelector(
    (state: RootState) => state.eventList.fetchError,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);

  const [filters, setFilters] = useState<EventFilters>({
    searchTerm: "",
    tradeIds: [],
    eventTypeIds: [],
    employeeRoleIds: [],
    categoryIds: [],
    tagIds: [],
  });

  const refetchEvents = useCallback(() => {
    const sortBy = sorting.length ? sorting[0].id : undefined;
    const sortOrder = sorting.length
      ? sorting[0].desc
        ? "desc"
        : "asc"
      : undefined;

    const tradeIdsNum = filters.tradeIds.map((v) => +v);
    const eventTypeIdsNum = filters.eventTypeIds.map((v) => +v);
    const roleIdsNum = filters.employeeRoleIds.map((v) => +v);
    const categoryIdsNum = filters.categoryIds.map((v) => +v);
    const tagIdsNum = filters.tagIds.map((v) => +v);

    dispatch(
      fetchEventsAction({
        searchTerm: filters.searchTerm || undefined,
        page: pagination.pageIndex + 1,
        pageSize: pagination.pageSize,
        sortBy,
        sortOrder: sortOrder as "asc" | "desc" | undefined,
        tradeIds: tradeIdsNum.length ? tradeIdsNum : undefined,
        eventTypeIds: eventTypeIdsNum.length ? eventTypeIdsNum : undefined,
        employeeRoleIds: roleIdsNum.length ? roleIdsNum : undefined,
        categoryIds: categoryIdsNum.length ? categoryIdsNum : undefined,
        tagIds: tagIdsNum.length ? tagIdsNum : undefined,
      }),
    );
  }, [sorting, filters, pagination, dispatch]);

  useEffect(() => {
    refetchEvents();
  }, [refetchEvents]);

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
    events,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    refetchEvents,
  };
};
