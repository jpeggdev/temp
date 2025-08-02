import { useState, useEffect } from "react";
import { useSubscription } from "@apollo/client";
import {
  CompanyDataImportJob,
  CompanyDataImportSubscriptionData,
} from "../graphql/subscriptions/onCompanyDataImportJob/types";
import { ON_COMPANY_DATA_IMPORT_JOB_SUBSCRIPTION } from "../graphql/subscriptions/onCompanyDataImportJob/onCompanyDataImportJobSubscription";

export interface PaginationState {
  pageIndex: number;
  pageSize: number;
}
export type VisibilityState = Record<string, boolean>;
export type OnChangeFn<T> = (updaterOrValue: T | ((old: T) => T)) => void;

interface SortingItem {
  id: string;
  desc?: boolean;
}
type SortingState = SortingItem[];

interface UseImportStatusReturn {
  items: CompanyDataImportJob[];
  loading: boolean;
  error: Error | null;
  pagination: PaginationState;
  sorting: SortingState;
  columnVisibility: VisibilityState;
  handlePaginationChange: OnChangeFn<PaginationState>;
  handleSortingChange: (newSorting: SortingState) => void;
  handleColumnVisibilityChange: OnChangeFn<VisibilityState>;
  totalCount: number;
}

export function useImportStatus(companyId: number): UseImportStatusReturn {
  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });
  const [sorting, setSorting] = useState<SortingState>([]);
  const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
  const [items, setItems] = useState<CompanyDataImportJob[]>([]);
  const [error, setError] = useState<Error | null>(null);

  const offset = pagination.pageIndex * pagination.pageSize;

  const {
    data,
    loading,
    error: subscriptionError,
  } = useSubscription<CompanyDataImportSubscriptionData>(
    ON_COMPANY_DATA_IMPORT_JOB_SUBSCRIPTION,
    {
      variables: {
        companyId,
        limit: pagination.pageSize,
        offset,
      },
      skip: companyId === 0,
    },
  );

  useEffect(() => {
    if (subscriptionError) {
      setError(subscriptionError as Error);
    }
  }, [subscriptionError]);

  useEffect(() => {
    if (data?.company_data_import_job) {
      setItems(data.company_data_import_job);
    }
  }, [data]);

  const handlePaginationChange: OnChangeFn<PaginationState> = (
    updaterOrValue,
  ) => {
    setPagination((oldPagination) => {
      if (typeof updaterOrValue === "function") {
        return updaterOrValue(oldPagination);
      }
      return updaterOrValue;
    });
  };

  const handleSortingChange = (newSorting: SortingState) => {
    setSorting(newSorting);
  };

  const handleColumnVisibilityChange: OnChangeFn<VisibilityState> = (
    updaterOrValue,
  ) => {
    setColumnVisibility((old) => {
      if (typeof updaterOrValue === "function") {
        return updaterOrValue(old);
      }
      return updaterOrValue;
    });
  };

  const totalCount = items.length;

  return {
    items,
    loading,
    error,
    pagination,
    sorting,
    columnVisibility,
    handlePaginationChange,
    handleSortingChange,
    handleColumnVisibilityChange,
    totalCount,
  };
}
