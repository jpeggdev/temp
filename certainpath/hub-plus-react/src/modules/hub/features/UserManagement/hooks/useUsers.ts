import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { fetchUsersAction } from "../slices/usersSlice";
import { RootState } from "../../../../../app/rootReducer";
import { FetchUsersRequest } from "../../../../../api/fetchUsers/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

export function useUsers() {
  const dispatch = useDispatch();
  const { users, totalCount, loading, error } = useSelector(
    (state: RootState) => state.users,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);

  const [filters, setFilters] = useState<{
    firstName?: string;
    lastName?: string;
    email?: string;
    salesforceId?: string;
  }>({});

  const requestData = React.useMemo<FetchUsersRequest>(() => {
    return {
      page: pagination.pageIndex + 1,
      sortBy: sorting.length > 0 ? sorting[0].id : undefined,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : undefined,
      firstName: filters.firstName,
      lastName: filters.lastName,
      email: filters.email,
      salesforceId: filters.salesforceId,
      pageSize: pagination.pageSize,
    };
  }, [pagination, sorting, filters]);

  useEffect(() => {
    dispatch(fetchUsersAction(requestData));
  }, [dispatch, requestData]);

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
    setFilters((prevFilters) => ({
      ...prevFilters,
      [filterKey]: value,
    }));
  };

  return {
    users,
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
