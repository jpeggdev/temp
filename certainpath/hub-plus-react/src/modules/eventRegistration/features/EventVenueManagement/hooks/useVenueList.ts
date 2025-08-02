import { useState, useEffect, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  SortingState,
  PaginationState,
  OnChangeFn,
} from "@tanstack/react-table";
import { RootState } from "@/app/rootReducer";
import { useNavigate } from "react-router-dom";
import { fetchVenuesAction } from "@/modules/eventRegistration/features/EventVenueManagement/slices/VenueListSlice";
import { FetchVenuesRequest } from "@/modules/eventRegistration/features/EventVenueManagement/api/fetchVenues/types";

export function useVenueList() {
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const { venues, totalCount, loading, error } = useSelector(
    (state: RootState) => state.venueList,
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

  const buildRequestData = useCallback((): FetchVenuesRequest => {
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

  const refetchVenues = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(fetchVenuesAction(requestData));
  }, [dispatch, buildRequestData]);

  useEffect(() => {
    refetchVenues();
  }, [refetchVenues]);

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

  const handleEditVenue = useCallback(
    (id: number) => {
      navigate(`/event-registration/admin/venue/${id}/edit`);
    },
    [navigate],
  );

  return {
    venues,
    totalCount,
    loading,
    error,
    filters,
    pagination,
    refetchVenues,
    handleFilterChange,
    handleEditVenue,
    handleSortingChange,
    handlePaginationChange,
  };
}
