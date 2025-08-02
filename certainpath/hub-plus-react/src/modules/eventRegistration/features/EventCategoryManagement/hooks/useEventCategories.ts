import { useEffect, useState, useCallback } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { fetchEventCategoriesAction } from "@/modules/eventRegistration/features/EventCategoryManagement/slice/eventCategorySlice";

interface FetchEventCategoriesRequest {
  searchTerm?: string;
  name?: string;
  isActive?: boolean;
  page?: number;
  pageSize?: number;
  sortBy?: string;
  sortOrder?: "ASC" | "DESC";
}

type EventCategoryFilters = {
  name: string;
};

export const useEventCategories = () => {
  const dispatch = useAppDispatch();

  const categories = useAppSelector(
    (state: RootState) => state.eventCategory.categoriesList,
  );
  const totalCount = useAppSelector(
    (state: RootState) => state.eventCategory.totalCount,
  );
  const loading = useAppSelector(
    (state: RootState) => state.eventCategory.loadingList,
  );
  const error = useAppSelector(
    (state: RootState) => state.eventCategory.listError,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<EventCategoryFilters>({
    name: "",
  });

  const buildRequestData = useCallback((): FetchEventCategoriesRequest => {
    const sortBy = sorting.length ? sorting[0].id : undefined;
    const sortOrder = sorting.length
      ? sorting[0].desc
        ? "DESC"
        : "ASC"
      : undefined;

    return {
      searchTerm: filters.name || undefined,
      page: pagination.pageIndex + 1,
      pageSize: pagination.pageSize,
      sortBy: sortBy || "name",
      sortOrder: sortOrder || "ASC",
    };
  }, [filters, pagination, sorting]);

  const refetchCategories = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(fetchEventCategoriesAction(requestData));
  }, [dispatch, buildRequestData]);

  useEffect(() => {
    refetchCategories();
  }, [refetchCategories]);

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
    categories,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    refetchCategories,
  };
};
