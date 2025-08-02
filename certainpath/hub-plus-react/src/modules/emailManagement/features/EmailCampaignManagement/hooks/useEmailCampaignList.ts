import { useState, useEffect, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  SortingState,
  PaginationState,
  OnChangeFn,
} from "@tanstack/react-table";
import { RootState } from "@/app/rootReducer";
import { FetchEmailCampaignsRequest } from "@/modules/emailManagement/features/EmailCampaignManagement/api/fetchEmailCampaigns/types";
import { fetchEmailCampaignsAction } from "@/modules/emailManagement/features/EmailCampaignManagement/slices/emailCampaignListSlice";
import { fetchEmailCampaignStatusesAction } from "@/modules/emailManagement/features/EmailCampaignManagement/slices/emailCampaignStatusesSlice";
import { useNavigate } from "react-router-dom";

export function useEmailCampaignList() {
  const navigate = useNavigate();
  const dispatch = useDispatch();

  const {
    emailCampaigns,
    totalCount: emailCampaignsTotalCount,
    loading: emailCampaignsLoading,
    error: emailCampaignsError,
  } = useSelector((state: RootState) => state.emailCampaignList);

  const {
    emailCampaignStatuses,
    loading: emailCampaignStatusesLoading,
    error: emailCampaignStatusesError,
  } = useSelector((state: RootState) => state.emailCampaignStatuses);

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);

  const [filters, setFilters] = useState<{
    searchTerm?: string;
    statusId?: number;
  }>({
    searchTerm: "",
    statusId: undefined,
  });

  const buildRequestData = useCallback((): FetchEmailCampaignsRequest => {
    const sortBy = sorting.length
      ? (sorting[0].id as "id" | "name")
      : undefined;

    const sortOrder = sorting.length
      ? sorting[0].desc
        ? "DESC"
        : "ASC"
      : undefined;

    return {
      page: pagination.pageIndex + 1,
      pageSize: pagination.pageSize,
      sortBy: sortBy,
      sortOrder: sortOrder,
      searchTerm: filters.searchTerm,
      emailCampaignStatusId: filters.statusId,
    };
  }, [filters, pagination, sorting]);

  const refetchEmailCampaigns = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(fetchEmailCampaignsAction(requestData));
  }, [dispatch, buildRequestData]);

  useEffect(() => {
    refetchEmailCampaigns();
    dispatch(fetchEmailCampaignStatusesAction({}));
  }, [dispatch, refetchEmailCampaigns]);

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
    (searchTerm: string, statusId?: number) => {
      setFilters({ searchTerm, statusId });
      handlePaginationChange({
        pageIndex: 0,
        pageSize: pagination.pageSize,
      });
    },
    [handlePaginationChange, pagination.pageSize],
  );

  const handleEdit = useCallback(
    (id: number) => {
      navigate(`/email-management/email-campaign/${id}/edit`);
    },
    [navigate],
  );

  return {
    emailCampaigns,
    emailCampaignStatuses,
    totalCount: emailCampaignsTotalCount,
    loading: emailCampaignsLoading || emailCampaignStatusesLoading,
    error: emailCampaignsError || emailCampaignStatusesError,
    filters,
    pagination,
    refetchEmailCampaigns,
    handleFilterChange,
    handleEdit,
    handleSortingChange,
    handlePaginationChange,
  };
}
