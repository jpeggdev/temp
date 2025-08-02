import { useState, useEffect, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  SortingState,
  PaginationState,
  OnChangeFn,
} from "@tanstack/react-table";
import { RootState } from "@/app/rootReducer";
import { useNavigate } from "react-router-dom";
import { fetchDiscountsAction } from "@/modules/eventRegistration/features/EventDiscountManagement/slices/DiscountListSlice";
import { FetchDiscountsRequest } from "@/modules/eventRegistration/features/EventDiscountManagement/api/fetchDiscounts/types";

export function useDiscountList() {
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const { discounts, totalCount, loading, error } = useSelector(
    (state: RootState) => state.discountList,
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

  const buildRequestData = useCallback((): FetchDiscountsRequest => {
    const sortBy = sorting.length
      ? (sorting[0].id as "id" | "name")
      : undefined;
    const sortOrder = sorting.length
      ? sorting[0].desc
        ? "DESC"
        : "ASC"
      : undefined;

    return {
      isActive: filters.isActive,
      searchTerm: filters.searchTerm,
      page: pagination.pageIndex + 1,
      pageSize: pagination.pageSize,
      sortBy: sortBy,
      sortOrder: sortOrder,
    };
  }, [filters, pagination, sorting]);

  const refetchDiscounts = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(fetchDiscountsAction(requestData));
  }, [dispatch, buildRequestData]);

  useEffect(() => {
    refetchDiscounts();
  }, [refetchDiscounts]);

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

  const handleEditDiscount = useCallback(
    (id: number) => {
      navigate(`/event-registration/admin/discount/${id}/edit`);
    },
    [navigate],
  );

  return {
    discounts,
    totalCount,
    loading,
    error,
    filters,
    pagination,
    refetchDiscounts,
    handleFilterChange,
    handleEditDiscount,
    handleSortingChange,
    handlePaginationChange,
  };
}
