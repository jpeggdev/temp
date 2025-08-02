import React, { useCallback, useMemo, useState } from "react";

// Components
import DataTable from "@/components/Datatable/Datatable";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import StochasticMailingListFilters from "../StochasticMailingListFilters/StochasticMailingListFilters";
import UpdateBatchStatusModal from "../UpdateBatchStatusModal/UpdateBatchStatusModal";

// Hooks
import { useStochasticMailingData } from "../../hooks/useStochasticMailingData";
import { useDownloadBatchesProspectsCsv } from "../../hooks/useDownloadBatchesProspectsCsv";
import { useBulkUpdateBatchesStatus } from "../../hooks/useBulkUpdateBatchesStatus";
import { useCreateBatchInvoices } from "../../hooks/useCreateBatchInvoices";
import { useDownloadSummaryCSV } from "../../hooks/useDownloadSummaryCSV";

// Utils and Types
import { createStochasticMailingColumns } from "../StochasticMailingListColumns/StochasticMailingListColumns";
import { StochasticClientMailDataRow } from "@/api/fetchStochasticClientMailData/types";

const StochasticMailingList: React.FC = () => {
  const {
    mailDataRows,
    totalCount,
    mailDataRowsLoading,
    mailDataRowsError,
    pagination,
    bulkBatchStatusDetailsMetadata,
    bulkBatchStatusDetailsMetadataLoading,
    filters,
    handlePaginationChange,
    handleFilterChange,
    sorting,
    handleSortingChange,
  } = useStochasticMailingData();
  const [showBillButton, setShowBillButton] = useState(false);
  const [checkedRows, setCheckedRows] = useState<Record<string, boolean>>({});
  const [isAllSelected, setIsAllSelected] = useState(false);
  const [modalState, setModalState] = useState({
    isOpen: false,
    selectedStatus: "new",
  });
  const [billingConfirmation, setBillingConfirmation] = useState({
    isOpen: false,
    count: 0,
  });

  const { loading: isDownloading, downloadCsv } =
    useDownloadBatchesProspectsCsv();
  const { loading: isBulkUpdating, bulkUpdateBatchesStatus } =
    useBulkUpdateBatchesStatus();
  const { loading: isBilling, billSelectedBatches } = useCreateBatchInvoices();
  const { loading: isDownloadingSummary, downloadSummaryCSV } =
    useDownloadSummaryCSV();

  const selectableRows = useMemo(() => {
    return mailDataRows.filter(
      (row) =>
        row.batchStatus === "processed" &&
        row.batchPricing?.actualQuantity != null,
    );
  }, [mailDataRows]);

  const handleCheckboxChange = useCallback(
    (rowId: string, checked: boolean) => {
      setCheckedRows((prev) => {
        const newCheckedRows = {
          ...prev,
          [rowId]: checked,
        };

        const hasSelectedRows = Object.values(newCheckedRows).some(
          (isChecked) => isChecked,
        );
        setShowBillButton(hasSelectedRows);

        const allSelected = selectableRows.every(
          (row) => newCheckedRows[row.id.toString()] === true,
        );
        setIsAllSelected(allSelected);

        return newCheckedRows;
      });
    },
    [selectableRows],
  );

  const handleSelectAllChange = useCallback(
    (checked: boolean) => {
      const newCheckedRows = { ...checkedRows };

      selectableRows.forEach((row) => {
        newCheckedRows[row.id.toString()] = checked;
      });

      setCheckedRows(newCheckedRows);
      setIsAllSelected(checked);
      setShowBillButton(checked && selectableRows.length > 0);
    },
    [selectableRows, checkedRows],
  );

  const handleBillBatchesButtonClick = useCallback(() => {
    const selectedCount = Object.keys(checkedRows).filter(
      (id) => checkedRows[id],
    ).length;

    setBillingConfirmation({
      isOpen: true,
      count: selectedCount,
    });
  }, [checkedRows]);

  const confirmBillBatches = useCallback(async () => {
    try {
      await billSelectedBatches(checkedRows, mailDataRows);
      setCheckedRows({});
      setShowBillButton(false);
      setIsAllSelected(false);
      if (filters.week) {
        handleFilterChange("week", filters.week);
      }
    } catch (error) {
      console.error("Billing error:", error);
    } finally {
      setBillingConfirmation({ isOpen: false, count: 0 });
    }
  }, [
    billSelectedBatches,
    checkedRows,
    mailDataRows,
    filters.week,
    handleFilterChange,
  ]);

  const cancelBillBatches = useCallback(() => {
    setBillingConfirmation({ isOpen: false, count: 0 });
  }, []);

  const handleUpdateBatchStatusButtonClick = useCallback(
    () =>
      setModalState({
        isOpen: true,
        selectedStatus: bulkBatchStatusDetailsMetadata?.currentStatus || "new",
      }),
    [bulkBatchStatusDetailsMetadata],
  );

  const handleModalCloseButtonClicked = useCallback(
    () => setModalState((prev) => ({ ...prev, isOpen: false })),
    [],
  );

  const handleUpdateStatus = useCallback(
    (newStatus: string) =>
      setModalState((prev) => ({ ...prev, selectedStatus: newStatus })),
    [],
  );

  const handleDownloadCsv = useCallback(() => {
    if (filters.week && filters.year) {
      downloadCsv(filters.week as number, filters.year as number);
    }
  }, [downloadCsv, filters]);

  const handleDownloadSummary = useCallback(() => {
    if (filters.week && filters.year) {
      downloadSummaryCSV(filters.week as number, filters.year as number);
    }
  }, [downloadSummaryCSV, filters]);

  const handleSaveButtonClicked = useCallback(async () => {
    if (!filters.week || !filters.year) return;

    await bulkUpdateBatchesStatus(
      filters.year as number,
      filters.week as number,
      modalState.selectedStatus,
    );

    handleModalCloseButtonClicked();
    handleFilterChange("week", filters.week);
  }, [
    bulkUpdateBatchesStatus,
    filters,
    modalState.selectedStatus,
    handleModalCloseButtonClicked,
    handleFilterChange,
  ]);

  const columns = useMemo(() => {
    const hasProcessedStatus = mailDataRows.some(
      (row) => row.batchStatus === "processed",
    );
    return createStochasticMailingColumns(
      hasProcessedStatus,
      checkedRows,
      handleCheckboxChange,
      isAllSelected,
      handleSelectAllChange,
    );
  }, [
    mailDataRows,
    checkedRows,
    handleCheckboxChange,
    isAllSelected,
    handleSelectAllChange,
  ]);

  const batchStatusOptions = useMemo(
    () => bulkBatchStatusDetailsMetadata?.bulkBatchStatusOptions || [],
    [bulkBatchStatusDetailsMetadata],
  );

  const isModalSaveButtonDisabled = useMemo(
    () =>
      modalState.selectedStatus ===
        bulkBatchStatusDetailsMetadata?.currentStatus || isBulkUpdating,
    [modalState.selectedStatus, bulkBatchStatusDetailsMetadata, isBulkUpdating],
  );

  return (
    <MainPageWrapper error={mailDataRowsError} title="Campaign Status">
      <StochasticMailingListFilters
        filters={filters}
        isBilling={isBilling}
        isDownloading={isDownloading}
        isDownloadingSummary={isDownloadingSummary}
        onBillBatchesButtonClick={handleBillBatchesButtonClick}
        onDownloadCLIFileButtonClick={handleDownloadCsv}
        onDownloadSummaryButtonClick={handleDownloadSummary}
        onFilterChange={handleFilterChange}
        onUpdateBatchStatusButtonClick={handleUpdateBatchStatusButtonClick}
        showBillButton={showBillButton}
      />

      <DataTable<StochasticClientMailDataRow>
        columns={columns}
        data={mailDataRows}
        error={mailDataRowsError}
        loading={mailDataRowsLoading || bulkBatchStatusDetailsMetadataLoading}
        noDataMessage="No campaign rows found"
        onPageChange={(newPageIndex, newPageSize) =>
          handlePaginationChange({
            pageIndex: newPageIndex,
            pageSize: newPageSize,
          })
        }
        onSortingChange={handleSortingChange}
        pageIndex={pagination.pageIndex}
        pageSize={pagination.pageSize}
        rowKeyExtractor={(row) =>
          row.id ?? `${row.campaignId}-${row.batchNumber}`
        }
        sorting={sorting}
        totalCount={totalCount}
      />

      <UpdateBatchStatusModal
        batchStatusOptions={batchStatusOptions}
        isModalOpen={modalState.isOpen}
        isSaveButtonDisabled={isModalSaveButtonDisabled}
        isSaving={isBulkUpdating}
        onCloseButtonClick={handleModalCloseButtonClicked}
        onSaveButtonClick={handleSaveButtonClicked}
        onStatusChange={handleUpdateStatus}
        selectedStatus={modalState.selectedStatus}
      />

      {billingConfirmation.isOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
            <h3 className="text-lg font-medium mb-4">Confirm Billing</h3>
            <p className="mb-2 text-lg">
              Are you sure you want to bill {billingConfirmation.count} selected
              batches?
            </p>
            <p className="mb-6 text-destructive">
              This action will invoice the selected batches for their campaigns
              and can not be undone.
            </p>
            <div className="flex justify-end space-x-3">
              <button
                className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                disabled={isBilling}
                onClick={cancelBillBatches}
              >
                Cancel
              </button>
              <button
                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                disabled={isBilling}
                onClick={confirmBillBatches}
              >
                {isBilling ? "Processing..." : "Confirm"}
              </button>
            </div>
          </div>
        </div>
      )}
    </MainPageWrapper>
  );
};

export default StochasticMailingList;
