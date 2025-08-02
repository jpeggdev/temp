import { useEffect, useState, useCallback } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { getResourceCategoriesAction } from "@/modules/hub/features/ResourceCategoryManagement/slice/resourceCategorySlice";

interface GetResourceCategoriesRequest {
  name?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

type CategoryFilters = {
  name: string;
};

export const useResourceCategories = () => {
  const dispatch = useAppDispatch();

  const categories = useAppSelector(
    (state: RootState) => state.resourceCategory.categoriesList,
  );
  const totalCount = useAppSelector(
    (state: RootState) => state.resourceCategory.totalCount,
  );
  const loading = useAppSelector(
    (state: RootState) => state.resourceCategory.loadingList,
  );
  const error = useAppSelector(
    (state: RootState) => state.resourceCategory.listError,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<CategoryFilters>({
    name: "",
  });

  const buildRequestData = useCallback((): GetResourceCategoriesRequest => {
    const sortBy = sorting.length
      ? (sorting[0].id as "id" | "name" | undefined)
      : undefined;

    const sortOrder = sorting.length
      ? sorting[0].desc
        ? "DESC"
        : "ASC"
      : undefined;

    return {
      name: filters.name || undefined,
      page: pagination.pageIndex + 1,
      pageSize: pagination.pageSize,
      sortBy,
      sortOrder,
    };
  }, [filters, pagination, sorting]);

  const refetchCategories = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(getResourceCategoriesAction(requestData));
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
