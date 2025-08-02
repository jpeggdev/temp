import { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  fetchStochasticCustomersAction,
  resetStochasticCustomers,
} from "../slices/stochasticCustomersSlice";
import { RootState } from "../../../../../app/rootReducer";
import { FetchStochasticCustomersRequest } from "../../../../../api/fetchStochasticCustomers/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";

export function useStochasticCustomers() {
  const dispatch = useDispatch();
  const { customers, totalCount, loading, error } = useSelector(
    (state: RootState) => state.stochasticCustomers,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);

  const [filters, setFilters] = useState<{
    searchTerm?: string;
    isActive: number;
  }>({
    searchTerm: "",
    isActive: 1,
  });

  useEffect(() => {
    const requestData: FetchStochasticCustomersRequest = {
      page: pagination.pageIndex + 1,
      sortBy: sorting.length > 0 ? sorting[0].id : undefined,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : undefined,
      searchTerm: filters.searchTerm,
      isActive: filters.isActive,
      pageSize: pagination.pageSize,
    };
    dispatch(fetchStochasticCustomersAction(requestData));
  }, [dispatch, pagination, sorting, filters]);

  useEffect(() => {
    return () => {
      dispatch(resetStochasticCustomers());
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

  const handleFilterChange = (searchTerm: string, isActive: number) => {
    setFilters({ searchTerm, isActive });
  };

  return {
    customers,
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
