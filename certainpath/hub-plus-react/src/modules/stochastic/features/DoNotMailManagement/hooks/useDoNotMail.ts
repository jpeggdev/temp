import { useState, useEffect, useCallback, useMemo } from "react";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import {
  fetchRestrictedAddressesAction,
  resetFetchRestrictedAddresses,
} from "../slices/fetchRestrictedAddressesSlice";
import { FetchRestrictedAddressesRequest } from "@/api/fetchRestrictedAddresses/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

interface DoNotMailFilters {
  externalId?: string;
  address1?: string;
  address2?: string;
  city?: string;
  stateCode?: string;
  postalCode?: string;
  countryCode?: string;
  isBusiness?: string;
  isVacant?: string;
  isVerified?: string;
}

export function useDoNotMail() {
  const dispatch = useDispatch();
  const { addresses, totalCount, loading, error } = useSelector(
    (state: RootState) => state.fetchRestrictedAddresses,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<DoNotMailFilters>({
    externalId: "",
    address1: "",
    address2: "",
    city: "",
    stateCode: "",
    postalCode: "",
    countryCode: "",
    isBusiness: "",
    isVacant: "",
    isVerified: "",
  });

  const sortOrder =
    sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : undefined;
  const sortBy = sorting.length > 0 ? (sorting[0].id as string) : undefined;

  const requestData: FetchRestrictedAddressesRequest = useMemo(() => {
    const isBusiness =
      filters.isBusiness === "true" || filters.isBusiness === "false"
        ? filters.isBusiness
        : undefined;
    const isVacant =
      filters.isVacant === "true" || filters.isVacant === "false"
        ? filters.isVacant
        : undefined;
    const isVerified =
      filters.isVerified === "true" || filters.isVerified === "false"
        ? filters.isVerified
        : undefined;

    return {
      externalId: filters.externalId || undefined,
      address1: filters.address1 || undefined,
      address2: filters.address2 || undefined,
      city: filters.city || undefined,
      stateCode: filters.stateCode || undefined,
      postalCode: filters.postalCode || undefined,
      countryCode: filters.countryCode || undefined,
      isBusiness,
      isVacant,
      isVerified,
      sortOrder,
      sortBy,
      page: pagination.pageIndex + 1,
      perPage: pagination.pageSize,
    };
  }, [
    filters.externalId,
    filters.address1,
    filters.address2,
    filters.city,
    filters.stateCode,
    filters.postalCode,
    filters.countryCode,
    filters.isBusiness,
    filters.isVacant,
    filters.isVerified,
    sortOrder,
    sortBy,
    pagination.pageIndex,
    pagination.pageSize,
  ]);

  useEffect(() => {
    dispatch(fetchRestrictedAddressesAction(requestData));
  }, [dispatch, requestData]);

  useEffect(() => {
    return () => {
      dispatch(resetFetchRestrictedAddresses());
    };
  }, [dispatch]);

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

  const handleFilterChange = useCallback((filterKey: string, value: string) => {
    setFilters((prev) => ({ ...prev, [filterKey]: value }));
    setPagination((prev) => ({ ...prev, pageIndex: 0 }));
  }, []);

  return {
    addresses,
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
