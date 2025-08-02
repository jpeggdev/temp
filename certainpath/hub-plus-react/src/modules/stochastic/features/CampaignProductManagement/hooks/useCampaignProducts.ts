import { useState, useEffect, useMemo } from "react";
import { useDispatch, useSelector } from "react-redux";
import { fetchCampaignProductsAction } from "../slices/campaignProductsSlice";
import { RootState } from "@/app/rootReducer";
import { CampaignProduct } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";

export function useCampaignProducts(refreshTrigger?: number) {
  const dispatch = useDispatch();
  const { campaignProducts, loading, error } = useSelector(
    (state: RootState) => state.stochasticCampaignProducts,
  );

  useEffect(() => {
    dispatch(fetchCampaignProductsAction());
  }, [dispatch, refreshTrigger]);

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<{ searchTerm?: string }>({});

  const filteredData = useMemo(() => {
    if (!filters.searchTerm) return campaignProducts;

    const searchTerm = filters.searchTerm.toLowerCase();
    return campaignProducts.filter((product: CampaignProduct) => {
      return (
        product.name?.toLowerCase().includes(searchTerm) ||
        product.description?.toLowerCase().includes(searchTerm)
      );
    });
  }, [campaignProducts, filters.searchTerm]);

  const filteredCount = filteredData.length;

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

  const handleFilterChange = (searchTerm: string) => {
    setFilters({ searchTerm });
    setPagination((prev) => ({
      ...prev,
      pageIndex: 0,
    }));
  };

  const paginatedData = useMemo(() => {
    const { pageIndex, pageSize } = pagination;
    const start = pageIndex * pageSize;
    const end = start + pageSize;
    return filteredData.slice(start, end);
  }, [filteredData, pagination]);

  return {
    campaignProducts: paginatedData,
    totalCount: filteredCount,
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
