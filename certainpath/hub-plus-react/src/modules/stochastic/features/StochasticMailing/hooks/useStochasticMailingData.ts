import { useState, useEffect, useMemo, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import { fetchStochasticMailDataAction } from "../slices/stochasticMailingSlice";
import { FetchStochasticClientMailDataRequest } from "@/api/fetchStochasticClientMailData/types";
import { fetchBulkBatchStatusDetailsMetadataAction } from "@/modules/stochastic/features/StochasticMailing/slices/bulkBatchStatusDetailsMetadataSlice";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

export function useStochasticMailingData() {
  const dispatch = useDispatch();

  const {
    mailDataRows,
    totalCount,
    loading: mailDataRowsLoading,
    error: mailDataRowsError,
  } = useSelector((state: RootState) => state.stochasticMailing);

  const {
    bulkBatchStatusDetailsMetadata,
    loading: bulkBatchStatusDetailsMetadataLoading,
    error: bulkBatchStatusDetailsMetadataError,
  } = useSelector((state: RootState) => state.bulkBatchStatusDetailsMetadata);

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<Record<string, unknown>>({
    week: 1,
    year: 2025,
  });

  const requestParams = useMemo<FetchStochasticClientMailDataRequest>(() => {
    const weekNum = (filters.week as number) ?? 1;
    const yearNum = (filters.year as number) ?? 2025;

    return {
      page: pagination.pageIndex + 1,
      perPage: pagination.pageSize,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : "DESC",
      week: weekNum,
      year: yearNum,
    };
  }, [pagination, sorting, filters]);

  useEffect(() => {
    dispatch(fetchStochasticMailDataAction(requestParams));
    dispatch(fetchBulkBatchStatusDetailsMetadataAction(requestParams));
  }, [dispatch, requestParams]);

  const handlePaginationChange: OnChangeFn<PaginationState> = useCallback(
    (updaterOrValue) => {
      setPagination((prev) =>
        typeof updaterOrValue === "function"
          ? updaterOrValue(prev)
          : updaterOrValue,
      );
    },
    [],
  );

  const handleSortingChange: OnChangeFn<SortingState> = useCallback(
    (updaterOrValue) => {
      setSorting((prev) =>
        typeof updaterOrValue === "function"
          ? updaterOrValue(prev)
          : updaterOrValue,
      );
    },
    [],
  );

  const handleFilterChange = useCallback((key: string, value: unknown) => {
    setFilters((prev) => ({ ...prev, [key]: value }));
    setPagination((prev) => ({ ...prev, pageIndex: 0 }));
  }, []);

  return {
    mailDataRows,
    mailDataRowsLoading,
    mailDataRowsError,
    totalCount,
    pagination,
    sorting,
    filters,
    bulkBatchStatusDetailsMetadata,
    bulkBatchStatusDetailsMetadataLoading,
    bulkBatchStatusDetailsMetadataError,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
  };
}
