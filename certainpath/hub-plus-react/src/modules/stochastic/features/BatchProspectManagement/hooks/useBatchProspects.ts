import { useState, useEffect, useMemo, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import { fetchBatchProspectsAction } from "../slices/batchProspectSlice";
import { RootState } from "../../../../../app/rootReducer";
import { GetBatchProspectsRequest } from "../../../../../api/getBatchProspects/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

export function useBatchProspects(batchId: number) {
  const dispatch = useDispatch();
  const { prospects, totalCount, loading, error } = useSelector(
    (state: RootState) => state.batchProspect,
  );

  const initialPagination = useMemo(
    () => ({
      pageIndex: 0,
      pageSize: 10,
    }),
    [],
  );

  const [pagination, setPagination] =
    useState<PaginationState>(initialPagination);
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<{ name?: string }>({});

  const requestData = useMemo<GetBatchProspectsRequest>(() => {
    return {
      page: pagination.pageIndex + 1,
      perPage: pagination.pageSize,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : "DESC",
    };
  }, [pagination, sorting]);

  useEffect(() => {
    if (batchId) {
      dispatch(fetchBatchProspectsAction(batchId, requestData));
    }
  }, [dispatch, batchId, requestData]);

  const handlePaginationChange: OnChangeFn<PaginationState> = useCallback(
    (updaterOrValue) => {
      setPagination((old) =>
        typeof updaterOrValue === "function"
          ? updaterOrValue(old)
          : updaterOrValue,
      );
    },
    [],
  );

  const handleSortingChange: OnChangeFn<SortingState> = useCallback(
    (updaterOrValue) => {
      setSorting((old) =>
        typeof updaterOrValue === "function"
          ? updaterOrValue(old)
          : updaterOrValue,
      );
    },
    [],
  );

  const handleFilterChange = useCallback((name: string) => {
    setFilters({ name });
    setPagination((prev) => ({ ...prev, pageIndex: 0 }));
  }, []);

  return {
    prospects,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
  };
}
