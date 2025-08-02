import { useEffect, useState, useCallback } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { getResourceTagsAction } from "../slice/resourceTagSlice";

interface GetResourceTagsRequest {
  name?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

type TagFilters = {
  name: string;
};

export const useResourceTags = () => {
  const dispatch = useAppDispatch();
  const tags = useAppSelector((state: RootState) => state.resourceTag.tagsList);
  const totalCount = useAppSelector(
    (state: RootState) => state.resourceTag.totalCount,
  );
  const loading = useAppSelector(
    (state: RootState) => state.resourceTag.loadingList,
  );
  const error = useAppSelector(
    (state: RootState) => state.resourceTag.listError,
  );
  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<TagFilters>({ name: "" });

  const buildRequestData = useCallback((): GetResourceTagsRequest => {
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

  const refetchTags = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(getResourceTagsAction(requestData));
  }, [dispatch, buildRequestData]);

  useEffect(() => {
    refetchTags();
  }, [refetchTags]);

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
    tags,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    refetchTags,
  };
};
