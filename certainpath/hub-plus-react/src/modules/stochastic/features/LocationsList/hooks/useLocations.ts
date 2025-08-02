import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import {
  OnChangeFn,
  PaginationState,
  SortingState,
} from "@tanstack/react-table";
import { useCallback, useEffect, useMemo, useState } from "react";
import { FetchLocationsRequest } from "@/modules/stochastic/features/LocationsList/api/fetchLocations/types";
import { fetchLocationsAction } from "@/modules/stochastic/features/LocationsList/slices/locationListSlice";

export function useLocations() {
  const dispatch = useDispatch();

  const { locations, totalCount, loading, error } = useSelector(
    (state: RootState) => state.locationList,
  );

  const [showCreateDrawer, setShowCreateDrawer] = useState(false);
  const [showEditDrawer, setShowEditDrawer] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const [editId, setEditId] = useState<number | null>(null);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const [sorting, setSorting] = useState<SortingState>([]);

  const [filters, setFilters] = useState<{
    searchTerm?: string;
    isActive: number;
  }>({
    searchTerm: "",
    isActive: 1,
  });

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

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

  const requestData = useMemo<FetchLocationsRequest>(
    () => ({
      page: pagination.pageIndex + 1,
      perPage: pagination.pageSize,
      sortBy: sorting.length > 0 ? sorting[0].id : undefined,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : undefined,
      searchTerm: filters.searchTerm,
      isActive: filters.isActive,
    }),
    [pagination, sorting, filters],
  );

  useEffect(() => {
    dispatch(fetchLocationsAction(requestData));
  }, [dispatch, requestData]);

  const refetchLocations = useCallback(() => {
    dispatch(fetchLocationsAction(requestData));
  }, [dispatch, requestData]);

  const openCreateDrawer = useCallback(() => {
    setShowCreateDrawer(true);
  }, []);

  const openEditDrawer = useCallback((id: number) => {
    setEditId(id);
    setShowEditDrawer(true);
  }, []);

  const openDeleteModal = useCallback((id: number) => {
    setDeleteId(id);
    setShowDeleteModal(true);
  }, []);

  const closeEditDrawer = useCallback(() => {
    setEditId(null);
    setShowEditDrawer(false);
  }, []);

  const closeDeleteModal = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
  }, []);

  const handleDeleteSuccessError = useCallback(() => {
    closeDeleteModal();
    refetchLocations();
  }, [closeDeleteModal, refetchLocations]);

  return {
    editId,
    deleteId,
    locations,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    showCreateDrawer,
    showEditDrawer,
    showDeleteModal,
    setShowCreateDrawer,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    refetchLocations,
    openCreateDrawer,
    openEditDrawer,
    openDeleteModal,
    closeEditDrawer,
    closeDeleteModal,
    handleDeleteSuccessError,
  };
}
