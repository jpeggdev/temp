import { useState, useEffect, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  SortingState,
  PaginationState,
  OnChangeFn,
} from "@tanstack/react-table";
import { RootState } from "@/app/rootReducer";
import { useNavigate } from "react-router-dom";
import { FetchVouchersRequest } from "@/modules/eventRegistration/features/EventVoucherManagement/api/fetchVouchers/types";
import { fetchVouchersAction } from "@/modules/eventRegistration/features/EventVoucherManagement/slices/VoucherListSlice";

export function useVoucherList() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { vouchers, totalCount, loading, error } = useSelector(
    (state: RootState) => state.voucherList,
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

  const buildRequestData = useCallback((): FetchVouchersRequest => {
    return {
      page: pagination.pageIndex + 1,
      pageSize: pagination.pageSize,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : "ASC",
      searchTerm: filters.searchTerm,
      isActive: filters.isActive,
    };
  }, [filters, pagination, sorting]);

  const refetchVouchers = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(fetchVouchersAction(requestData));
  }, [dispatch, buildRequestData]);

  useEffect(() => {
    refetchVouchers();
  }, [refetchVouchers]);

  const handlePaginationChange: OnChangeFn<PaginationState> = useCallback(
    (updaterOrValue) => {
      setPagination(updaterOrValue);
    },
    [],
  );

  const handleSortingChange: OnChangeFn<SortingState> = useCallback(
    (updaterOrValue) => {
      setSorting(updaterOrValue);
    },
    [],
  );

  const handleFilterChange = useCallback(
    (searchTerm: string, isActive: number) => {
      setFilters({ searchTerm, isActive });
      handlePaginationChange({
        pageIndex: 0,
        pageSize: pagination.pageSize,
      });
    },
    [handlePaginationChange, pagination.pageSize],
  );

  const handleEditVoucher = useCallback(
    (id: number) => {
      navigate(`/event-registration/admin/voucher/${id}/edit`);
    },
    [navigate],
  );

  return {
    vouchers,
    totalCount,
    loading,
    error,
    filters,
    pagination,
    refetchVouchers,
    handleFilterChange,
    handleSortingChange,
    handlePaginationChange,
    handleEditVoucher,
  };
}
