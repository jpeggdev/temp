import { useEffect, useState } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { fetchResourcesAction } from "../../slices/resourceListSlice";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

interface GetResourcesRequest {
  searchTerm?: string;
  page: number;
  pageSize: number;
  sortBy?: string;
  sortOrder?: "ASC" | "DESC" | "asc" | "desc";
  tradeIds?: number[];
  resourceTypeIds?: number[];
  employeeRoleIds?: number[];
}

type ResourceFilters = {
  searchTerm: string;
  tradeIds: string[];
  resourceTypeIds: string[];
  employeeRoleIds: string[];
};

export const useResources = () => {
  const dispatch = useAppDispatch();

  const resources = useAppSelector(
    (state: RootState) => state.resourceList.resources,
  );
  const totalCount = useAppSelector(
    (state: RootState) => state.resourceList.totalCount,
  );
  const loading = useAppSelector(
    (state: RootState) => state.resourceList.fetchLoading,
  );
  const error = useAppSelector(
    (state: RootState) => state.resourceList.fetchError,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<ResourceFilters>({
    searchTerm: "",
    tradeIds: [],
    resourceTypeIds: [],
    employeeRoleIds: [],
  });
  const [columnVisibility] = useState<{
    [key: string]: boolean;
  }>({});

  useEffect(() => {
    const sortBy = sorting.length ? sorting[0].id : undefined;
    const sortOrder = sorting.length
      ? sorting[0].desc
        ? "DESC"
        : "ASC"
      : undefined;

    const tradesNum = filters.tradeIds.map((v) => +v);
    const resourceTypesNum = filters.resourceTypeIds.map((v) => +v);
    const rolesNum = filters.employeeRoleIds.map((v) => +v);

    const requestData: GetResourcesRequest = {
      searchTerm: filters.searchTerm,
      page: pagination.pageIndex + 1,
      pageSize: pagination.pageSize,
      sortBy,
      sortOrder,
      tradeIds: tradesNum.length ? tradesNum : undefined,
      resourceTypeIds: resourceTypesNum.length ? resourceTypesNum : undefined,
      employeeRoleIds: rolesNum.length ? rolesNum : undefined,
    };

    dispatch(fetchResourcesAction(requestData));
  }, [dispatch, pagination, sorting, filters]);

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

  const handleFilterChange = (filterKey: string, value: string | string[]) => {
    setFilters((prev) => ({
      ...prev,
      [filterKey]: value,
    }));
  };

  return {
    resources,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    columnVisibility,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
  };
};
