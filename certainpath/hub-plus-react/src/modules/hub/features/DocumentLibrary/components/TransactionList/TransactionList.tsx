import React, { useMemo } from "react";
import { ReportType } from "../../../../../../api/fetchQuickBooksReports/types";
import { useQuickBooksReports } from "../../hooks/useQuickBooksReports";
import { createQuickBooksReportsColumns } from "../QuickBooksReportsColumns/QuickBooksReportsColumns";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import DataTable from "@/components/Datatable/Datatable";

const TransactionList: React.FC = () => {
  const {
    reports,
    totalCount,
    loading,
    error,
    pagination,
    handlePaginationChange,
    handleDownloadReport,
  } = useQuickBooksReports(ReportType.TRANSACTION_LIST);

  const columns = useMemo(
    () => createQuickBooksReportsColumns({ handleDownloadReport }),
    [handleDownloadReport],
  );

  return (
    <MainPageWrapper error={error} title="Transaction List Report">
      <DataTable
        columns={columns}
        data={reports}
        error={error}
        loading={loading}
        noDataMessage="No reports found"
        onPageChange={(newPageIndex, newPageSize) =>
          handlePaginationChange({
            pageIndex: newPageIndex,
            pageSize: newPageSize,
          })
        }
        pageIndex={pagination.pageIndex}
        pageSize={pagination.pageSize}
        rowKeyExtractor={(item) => item.uuid}
        totalCount={totalCount}
      />
    </MainPageWrapper>
  );
};

export default TransactionList;
