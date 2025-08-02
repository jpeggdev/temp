import { useState, useEffect, useMemo, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import { fetchCampaignFilesAction } from "../slices/campaignFilesSlice";
import { RootState } from "../../../../../app/rootReducer";
import { FetchCampaignFilesRequest } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignFiles/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

export function useCampaignFiles(campaignId: number) {
  const dispatch = useDispatch();
  const { files, totalCount, loading, error } = useSelector(
    (state: RootState) => state.campaignFiles,
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
  const [filters, setFilters] = useState<{ searchTerm?: string }>({});

  const requestData = useMemo<FetchCampaignFilesRequest>(() => {
    return {
      page: pagination.pageIndex + 1,
      pageSize: pagination.pageSize,
      sortBy: sorting.length > 0 ? sorting[0].id : undefined,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "desc" : "asc") : undefined,
      searchTerm: filters.searchTerm,
    };
  }, [pagination, sorting, filters]);

  useEffect(() => {
    dispatch(fetchCampaignFilesAction(campaignId, requestData));
  }, [dispatch, campaignId, requestData]);

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

  const handleFilterChange = useCallback((searchTerm: string) => {
    setFilters({ searchTerm });
    setPagination((prev) => ({ ...prev, pageIndex: 0 }));
  }, []);

  return {
    files,
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
