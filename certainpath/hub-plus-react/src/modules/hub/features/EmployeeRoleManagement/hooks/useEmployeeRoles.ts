import { useEffect, useState, useCallback } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { getEmployeeRolesAction } from "@/modules/hub/features/EmployeeRoleManagement/slice/employeeRoleSlice";

interface GetEmployeeRolesRequest {
  name?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

type EmployeeRoleFilters = {
  name: string;
};

export const useEmployeeRoles = () => {
  const dispatch = useAppDispatch();

  const roles = useAppSelector(
    (state: RootState) => state.employeeRole.rolesList,
  );
  const totalCount = useAppSelector(
    (state: RootState) => state.employeeRole.totalCount,
  );
  const loading = useAppSelector(
    (state: RootState) => state.employeeRole.loadingList,
  );
  const error = useAppSelector(
    (state: RootState) => state.employeeRole.listError,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<EmployeeRoleFilters>({ name: "" });

  const buildRequestData = useCallback((): GetEmployeeRolesRequest => {
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

  const refetchRoles = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(getEmployeeRolesAction(requestData));
  }, [buildRequestData, dispatch]);

  useEffect(() => {
    refetchRoles();
  }, [refetchRoles]);

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
    roles,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    refetchRoles,
  };
};
