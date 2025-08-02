import { useState, useEffect, useMemo, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  SortingState,
  PaginationState,
  OnChangeFn,
} from "@tanstack/react-table";
import { RootState } from "@/app/rootReducer";
import { FetchEmailCampaignEventLogsRequest } from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogs/types";
import { fetchEmailCampaignLogsAction } from "@/modules/emailManagement/features/EmailEventLogsManagement/slices/emailCampaignEventLogListSlice";
import { fetchEmailCampaignEventLogsMetadataAction } from "@/modules/emailManagement/features/EmailEventLogsManagement/slices/emailCampaignEventLogsMetadataSlice";
import {
  emailEventPeriodFilter,
  FetchEmailCampaignEventLogsMetadataRequest,
} from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogMetadata/types";

export function useEmailCampaignEventLogs() {
  const dispatch = useDispatch();

  const {
    emailCampaignEventLogs,
    totalCount: emailCampaignEventLogsTotalCount,
    loading: emailCampaignEventLogsLoading,
    error: emailCampaignEventLogsError,
  } = useSelector((state: RootState) => state.emailCampaignEventLogList);

  const {
    emailCampaignEventLogsMetadata,
    loading: emailCampaignEventLogsMetadataLoading,
    error: emailCampaignEventLogsMetadataError,
  } = useSelector((state: RootState) => state.emailCampaignEventLogsMetadata);

  const [emailEventPeriod, setEmailEventPeriod] =
    useState<emailEventPeriodFilter>("today");

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

  const requestData = useMemo<FetchEmailCampaignEventLogsRequest>(() => {
    const request: FetchEmailCampaignEventLogsRequest = {
      page: pagination.pageIndex + 1,
      perPage: pagination.pageSize,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : "ASC",
      searchTerm: filters.searchTerm,
      emailEventPeriodFilter: emailEventPeriod,
    };

    return request;
  }, [emailEventPeriod, pagination, sorting, filters]);

  const metadataRequestData =
    useMemo<FetchEmailCampaignEventLogsMetadataRequest>(() => {
      const request: FetchEmailCampaignEventLogsMetadataRequest = {
        emailEventPeriodFilter: emailEventPeriod,
      };

      return request;
    }, [emailEventPeriod, filters]);

  useEffect(() => {
    dispatch(fetchEmailCampaignLogsAction(requestData));
  }, [dispatch, requestData]);

  useEffect(() => {
    dispatch(fetchEmailCampaignEventLogsMetadataAction(metadataRequestData));
  }, [dispatch, emailEventPeriod]);

  const handlePaginationChange: OnChangeFn<PaginationState> = useCallback(
    (updaterOrValue) => {
      setPagination(updaterOrValue);
    },
    [],
  );

  const handleSortingChange: OnChangeFn<SortingState> = (updaterOrValue) => {
    setSorting((old) =>
      typeof updaterOrValue === "function"
        ? updaterOrValue(old)
        : updaterOrValue,
    );
  };

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

  return {
    emailCampaignEventLogs,
    emailCampaignEventLogsMetadata,
    emailCampaignEventLogsTotalCount,
    loading:
      emailCampaignEventLogsLoading || emailCampaignEventLogsMetadataLoading,
    error: emailCampaignEventLogsError || emailCampaignEventLogsMetadataError,
    filters,
    pagination,
    handleFilterChange,
    handleSortingChange,
    handlePaginationChange,
    emailEventPeriod,
    setEmailEventPeriod,
  };
}
