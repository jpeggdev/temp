import { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "../../../../../app/rootReducer";
import { fetchCompaniesAction } from "../slices/companiesSlice";
import { FetchCompaniesRequest } from "../../../../../api/fetchCompanies/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";

export const useCompanies = () => {
  const dispatch = useDispatch();

  const companies = useSelector(
    (state: RootState) => state.companies.companies,
  );
  const totalCount = useSelector(
    (state: RootState) => state.companies.totalCount,
  );
  const loading = useSelector((state: RootState) => state.companies.loading);
  const error = useSelector((state: RootState) => state.companies.error);

  const [pagination, setPagination] = useState({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);

  const [filters, setFilters] = useState<{ [key: string]: string }>({
    searchTerm: "",
  });
  const [columnVisibility] = useState<{
    [key: string]: boolean;
  }>({});

  useEffect(() => {
    const requestData: FetchCompaniesRequest = {
      ...filters,
      page: pagination.pageIndex + 1,
      pageSize: pagination.pageSize,
      sortBy: sorting.length > 0 ? sorting[0].id : undefined,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : undefined,
    };
    dispatch(fetchCompaniesAction(requestData));
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

  const handleFilterChange = (filterKey: string, value: string) => {
    setFilters((prevFilters) => ({
      ...prevFilters,
      [filterKey]: value,
    }));
  };

  return {
    companies,
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
