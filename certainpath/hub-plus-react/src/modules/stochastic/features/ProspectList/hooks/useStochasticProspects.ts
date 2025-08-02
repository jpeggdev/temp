import { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  fetchStochasticProspectsAction,
  resetStochasticProspects,
} from "../slices/stochasticProspectsSlice";
import { RootState } from "../../../../../app/rootReducer";
import { FetchStochasticProspectsRequest } from "../../../../../api/fetchStochasticProspects/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

export function useStochasticProspects() {
  const dispatch = useDispatch();
  const { prospects, totalCount, loading, error } = useSelector(
    (state: RootState) => state.stochasticProspects,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<{ searchTerm?: string }>({});

  useEffect(() => {
    const requestData: FetchStochasticProspectsRequest = {
      page: pagination.pageIndex + 1,
      sortBy: sorting.length > 0 ? sorting[0].id : undefined,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : undefined,
      searchTerm: filters.searchTerm,
      pageSize: pagination.pageSize,
    };
    dispatch(fetchStochasticProspectsAction(requestData));
  }, [dispatch, pagination, sorting, filters]);

  useEffect(() => {
    return () => {
      dispatch(resetStochasticProspects());
    };
  }, [dispatch]);

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

  const handleFilterChange = (searchTerm: string) => {
    setFilters({ searchTerm });
  };

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
