import { useEffect, useState, useCallback } from "react";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { fetchEmailTemplateCategoriesAction } from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/slice/emailTemplateCategorySlice";

interface FetchEmailTemplateCategoriesRequest {
  name?: string;
  page?: number;
  pageSize?: number;
  sortBy?: "id" | "name";
  sortOrder?: "ASC" | "DESC";
}

type EmailTemplateCategoryFilters = {
  name: string;
};

export const useEmailTemplateCategories = () => {
  const dispatch = useAppDispatch();

  const categories = useAppSelector(
    (state: RootState) => state.emailTemplateCategory.categoriesList,
  );
  const totalCount = useAppSelector(
    (state: RootState) => state.emailTemplateCategory.totalCount,
  );
  const loading = useAppSelector(
    (state: RootState) => state.emailTemplateCategory.loadingList,
  );
  const error = useAppSelector(
    (state: RootState) => state.emailTemplateCategory.listError,
  );

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [filters, setFilters] = useState<EmailTemplateCategoryFilters>({
    name: "",
  });

  const buildRequestData =
    useCallback((): FetchEmailTemplateCategoriesRequest => {
      const sortBy = sorting.length
        ? (sorting[0].id as "id" | "name")
        : undefined;
      const sortOrder = sorting.length
        ? sorting[0].desc
          ? "DESC"
          : "ASC"
        : undefined;

      return {
        name: filters.name || undefined,
        page: pagination.pageIndex + 1,
        pageSize: pagination.pageSize,
        sortBy,
        sortOrder,
      };
    }, [filters, pagination, sorting]);

  const refetchCategories = useCallback(() => {
    const requestData = buildRequestData();
    dispatch(fetchEmailTemplateCategoriesAction(requestData));
  }, [dispatch, buildRequestData]);

  useEffect(() => {
    refetchCategories();
  }, [refetchCategories]);

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
    setFilters((prev) => ({
      ...prev,
      [filterKey]: value,
    }));
    setPagination((prev) => ({ ...prev, pageIndex: 0 }));
  };

  return {
    categories,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    refetchCategories,
  };
};
