import { useEffect, useState, useCallback } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { searchEventInstructorsAction } from "@/modules/eventRegistration/features/EventInstructorManagement/slices/eventInstructorSlice";

interface SearchEventInstructorsRequestParams {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name" | "email" | "phone";
  sortOrder?: "ASC" | "DESC";
}

type EventInstructorFilters = {
  searchTerm: string;
};

export const useEventInstructors = () => {
  const dispatch = useAppDispatch();

  const instructors = useAppSelector(
    (state: RootState) => state.eventInstructor.instructors,
  );
  const totalCount = useAppSelector(
    (state: RootState) => state.eventInstructor.totalCount,
  );
  const loading = useAppSelector(
    (state: RootState) => state.eventInstructor.searchLoading,
  );
  const error = useAppSelector(
    (state: RootState) => state.eventInstructor.searchError,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<EventInstructorFilters>({
    searchTerm: "",
  });

  const buildRequestData =
    useCallback((): SearchEventInstructorsRequestParams => {
      let sortBy: "id" | "name" | "email" | "phone" | undefined;
      if (sorting.length) {
        const field = sorting[0].id;
        if (
          field === "id" ||
          field === "name" ||
          field === "email" ||
          field === "phone"
        ) {
          sortBy = field;
        }
      }

      const sortOrder = sorting.length
        ? sorting[0].desc
          ? "DESC"
          : "ASC"
        : undefined;

      return {
        searchTerm: filters.searchTerm || undefined,
        page: pagination.pageIndex + 1,
        pageSize: pagination.pageSize,
        sortBy: sortBy || "name",
        sortOrder: sortOrder || "ASC",
      };
    }, [filters, pagination, sorting]);

  const refetchInstructors = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(searchEventInstructorsAction(requestData));
  }, [dispatch, buildRequestData]);

  useEffect(() => {
    refetchInstructors();
  }, [refetchInstructors]);

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

  const handleFilterChange = (filterKey: string, value: string) => {
    setFilters((prev) => ({
      ...prev,
      [filterKey]: value,
    }));
    setPagination((prev) => ({ ...prev, pageIndex: 0 }));
  };

  return {
    instructors,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    refetchInstructors,
  };
};
