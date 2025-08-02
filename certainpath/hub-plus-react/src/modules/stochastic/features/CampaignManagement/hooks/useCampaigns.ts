import { useState, useEffect, useMemo, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import { fetchCampaignsAction } from "../slices/campaignListSlice";
import { RootState } from "@/app/rootReducer";
import { FetchCompanyCampaignsRequest } from "@/api/fetchCompanyCampaigns/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

export function useCampaigns() {
  const dispatch = useDispatch();
  const { campaigns, totalCount, loading, error } = useSelector(
    (state: RootState) => state.campaignList,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<{ name?: string }>({});
  const [campaignStatusId, setCampaignStatusId] = useState<number | undefined>(
    1,
  );


  const requestData = useMemo<FetchCompanyCampaignsRequest>(() => {
    return {
      page: pagination.pageIndex + 1,
      perPage: pagination.pageSize,
      sortOrder:
        sorting.length > 0 ? (sorting[0].desc ? "DESC" : "ASC") : "ASC",
      campaignStatusId,
    };
  }, [pagination, sorting, campaignStatusId]);

  useEffect(() => {
    dispatch(fetchCampaignsAction(requestData));
  }, [dispatch, requestData]);

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

  const handleFilterChange = useCallback((name: string) => {
    setFilters({ name });
    setPagination((prev) => ({ ...prev, pageIndex: 0 }));
  }, []);

  const handleCampaignStatusChange = useCallback((newStatusId?: number) => {
    setCampaignStatusId(newStatusId);
    setPagination((prev) => ({ ...prev, pageIndex: 0 }));
  }, []);



  const refetchCampaigns = () => {
    dispatch(fetchCampaignsAction(requestData));
  };

  return {
    campaigns,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    handleCampaignStatusChange,
    campaignStatusId,
    refetchCampaigns,
  };
}
