import { useState, useEffect, useMemo, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import { fetchCampaignBatchesAction } from "../slices/campaignBatchListSlice";
import { RootState } from "@/app/rootReducer";
import { GetCampaignBatchesRequest } from "@/api/getCampaignBatches/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

export function useCampaignBatches(campaignId: number) {
  const dispatch = useDispatch();
  const { batches, totalCount, loading, error } = useSelector(
    (state: RootState) => state.campaignBatchList,
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
  const [batchStatusId, setBatchStatusId] = useState<number | undefined>(1);

  // Memoize requestData to avoid re-triggering useEffect due to object identity changes
  const requestData = useMemo<GetCampaignBatchesRequest>(() => {
    return {
      page: pagination.pageIndex + 1,
      perPage: pagination.pageSize,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : "DESC",
      batchStatusId: batchStatusId,
    };
  }, [pagination, sorting, batchStatusId]);

  const fetchBatches = useCallback(() => {
    dispatch(fetchCampaignBatchesAction(campaignId, requestData));
  }, [dispatch, campaignId, requestData]);

  useEffect(fetchBatches, [fetchBatches]);

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

  const handleBatchStatusChange = useCallback((newStatusId?: number) => {
    setBatchStatusId(newStatusId);
    setPagination((prev) => ({ ...prev, pageIndex: 0 }));
  }, []);

  return {
    batches,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    handleBatchStatusChange,
    batchStatusId,
    fetchBatches,
  };
}
