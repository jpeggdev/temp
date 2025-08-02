import { useCallback, useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { ReportType } from "../../../../../api/fetchQuickBooksReports/types";
import { RootState } from "../../../../../app/rootReducer";
import {
  downloadQuickBooksReportAction,
  fetchQuickBooksReportsAction,
} from "../slices/quickBooksReportsSlice";
import { OnChangeFn, SortingState } from "@/components/Datatable/types";
import { PaginationState } from "@/components/Datatable/components/DataTablePagination/types";

export const useQuickBooksReports = (reportType: ReportType) => {
  const dispatch = useDispatch();

  const reports = useSelector(
    (state: RootState) => state.quickBooksReports.reports,
  );
  const totalCount = useSelector(
    (state: RootState) => state.quickBooksReports.totalCount,
  );
  const loading = useSelector(
    (state: RootState) => state.quickBooksReports.loading,
  );
  const error = useSelector(
    (state: RootState) => state.quickBooksReports.error,
  );

  const [sorting, setSortingState] = useState<SortingState>([]);
  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: 0,
    pageSize: 10,
  });

  const handleSortingChange: OnChangeFn<SortingState> = setSortingState;
  const handlePaginationChange: OnChangeFn<PaginationState> = setPagination;

  useEffect(() => {
    const fetchReports = () => {
      dispatch(
        fetchQuickBooksReportsAction({
          reportType,
          page: pagination.pageIndex + 1,
          pageSize: pagination.pageSize,
          sortOrder: sorting[0]?.desc ? "DESC" : "ASC",
        }),
      );
    };

    fetchReports();
  }, [
    dispatch,
    pagination.pageIndex,
    pagination.pageSize,
    sorting,
    reportType,
  ]);

  const handleDownloadReport = useCallback(
    (reportId: string) => {
      dispatch(downloadQuickBooksReportAction({ reportId }));
    },
    [dispatch],
  );

  return {
    reports,
    totalCount,
    loading,
    error,
    sorting,
    pagination,
    handleSortingChange,
    handlePaginationChange,
    handleDownloadReport,
  };
};
