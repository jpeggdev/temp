import React, { useCallback, useEffect, useMemo, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { createCampaignBatchesColumns } from "../CampaignBatchColumns/CampaignBatchColumns";
import { useDownloadBatchProspectsCsv } from "../../../BatchProspectManagement/hooks/useDownloadBatchProspectsCsv";
import { useArchiveBatch } from "@/modules/stochastic/features/CampaignBatchManagement/hooks/useArchiveBatch";
import Modal from "react-modal";
import CampaignBatchListFilters from "@/modules/stochastic/features/CampaignBatchManagement/components/CampaignBatchListFilter/CampaignBatchListFilters";
import { CampaignStatus } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignStatuses/types";
import { fetchBatchStatuses } from "@/api/fetchBatchStatuses/fetchBatchStatuses";
import { useCampaignBatches } from "@/modules/stochastic/features/CampaignBatchManagement/hooks/useCampaignBatches";
import { Batch } from "../../../../../../api/getCampaignBatches/types";
import DataTable from "@/components/Datatable/Datatable";
import { useCampaignInfo } from "@/modules/stochastic/shared/hooks/useCampaignInfo";

const CampaignBatchList: React.FC = () => {
  const { campaignId } = useParams<{ campaignId: string }>();
  const navigate = useNavigate();
  const numericCampaignId = Number(campaignId);

  const {
    campaign,
    loading: loadingCampaign,
    error: errorCampaign,
  } = useCampaignInfo(numericCampaignId);

  const [showArchiveBatchModal, setShowArchiveBatchModal] = useState(false);
  const [isArchivingBatch, setIsArchivingBatch] = useState(false);
  const [batchIdToArchive, setBatchIdToArchive] = useState<number | null>(null);

  const {
    batches,
    totalCount,
    loading,
    error,
    pagination,
    handlePaginationChange,
    sorting,
    handleBatchStatusChange,
    batchStatusId,
    fetchBatches,
    handleSortingChange,
  } = useCampaignBatches(numericCampaignId);

  const [batchStatuses, setBatchStatuses] = useState<CampaignStatus[]>([]);

  const {
    downloadCsv,
    loading: csvLoading,
    error: csvError,
  } = useDownloadBatchProspectsCsv();

  const {
    archiveBatch,
    loading: archiveBatchLoading,
    error: archiveBatchError,
  } = useArchiveBatch();

  const handleViewProspects = useCallback(
    (batchId: number) => {
      navigate(
        `/stochastic/campaigns/${campaignId}/batches/${batchId}/prospects`,
      );
    },
    [navigate, campaignId],
  );

  const handleDownloadCsv = useCallback(
    (batchId: number) => {
      downloadCsv(batchId);
    },
    [downloadCsv],
  );

  const handleOpenArchiveBatchModal = useCallback((batchId: number) => {
    setShowArchiveBatchModal(true);
    setBatchIdToArchive(batchId);
  }, []);

  const handleCloseArchiveBatchModal = () => {
    setShowArchiveBatchModal(false);
    setBatchIdToArchive(null);
  };

  const handleArchiveBatchClick = async () => {
    if (batchIdToArchive === null) {
      console.error("No batch ID selected for archiving.");
      return;
    }
    setIsArchivingBatch(true);
    try {
      await archiveBatch(batchIdToArchive);
      handleCloseArchiveBatchModal();
      fetchBatches(); // Refresh
    } catch (error) {
      console.error("Failed to archive batch:", error);
    } finally {
      setIsArchivingBatch(false);
    }
  };

  const columns = useMemo(
    () =>
      createCampaignBatchesColumns({
        handleViewProspects,
        handleDownloadCsv,
        handleOpenArchiveBatchModal,
      }),
    [handleViewProspects, handleDownloadCsv, handleOpenArchiveBatchModal],
  );

  useEffect(() => {
    const loadStatuses = async () => {
      try {
        const response = await fetchBatchStatuses();
        setBatchStatuses(response.data);
      } catch (err) {
        console.error("Failed to fetch batch statuses", err);
      }
    };
    loadStatuses();
  }, []);

  const handleFilterChange = useCallback(
    (newStatusId?: number) => {
      handleBatchStatusChange(newStatusId);
    },
    [handleBatchStatusChange],
  );

  const combinedError = error || csvError || archiveBatchError || errorCampaign;

  const combinedLoading =
    loading || csvLoading || archiveBatchLoading || loadingCampaign;

  const manualBreadcrumbs = useMemo(() => {
    if (!campaignId) return undefined;
    const campaignName = campaign?.name ?? `Campaign ${campaignId}`;

    return [
      { path: "/stochastic", label: "Stochastic Dashboard" },
      { path: "/stochastic/campaigns", label: "Campaigns" },
      {
        path: `/stochastic/campaigns/${campaignId}/batches`,
        label: `${campaignName} Batches`,
        clickable: false,
      },
    ];
  }, [campaignId, campaign]);

  return (
    <MainPageWrapper
      error={combinedError}
      manualBreadcrumbs={manualBreadcrumbs}
      title="Campaign Batches"
    >
      <CampaignBatchListFilters
        batchStatusId={batchStatusId}
        batchStatuses={batchStatuses}
        onFilterChange={handleFilterChange}
      />

      <DataTable<Batch>
        columns={columns}
        data={batches}
        error={combinedError}
        loading={combinedLoading}
        noDataMessage="No batches found"
        onPageChange={(newPageIndex, newPageSize) =>
          handlePaginationChange({
            pageIndex: newPageIndex,
            pageSize: newPageSize,
          })
        }
        onSortingChange={handleSortingChange}
        pageIndex={pagination.pageIndex}
        pageSize={pagination.pageSize}
        rowKeyExtractor={(item) => item.id}
        sorting={sorting}
        totalCount={totalCount}
      />

      <Modal
        isOpen={showArchiveBatchModal}
        onRequestClose={handleCloseArchiveBatchModal}
        style={{
          content: {
            top: "50%",
            left: "50%",
            right: "auto",
            bottom: "auto",
            transform: "translate(-50%, -50%)",
            borderRadius: "8px",
            padding: "24px",
            width: "500px",
            background: "white",
            boxShadow: "0 10px 25px rgba(0,0,0,.3)",
          },
          overlay: {
            backgroundColor: "rgba(0,0,0,0.3)",
            zIndex: 9999,
          },
        }}
      >
        <h3 className="text-xl font-semibold text-gray-900">Archive Batch</h3>
        <p className="mt-3 text-sm text-gray-600">
          Are you sure you want to archive this batch?
          <br />
          <br />
          Note: This batch will be automatically recreated during the next
          iteration.
        </p>

        <div className="mt-6 flex justify-end space-x-3">
          <button
            className="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            onClick={handleCloseArchiveBatchModal}
            type="button"
          >
            Cancel
          </button>

          <button
            className="inline-flex justify-center rounded-md bg-red-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-600"
            disabled={isArchivingBatch}
            onClick={handleArchiveBatchClick}
            type="button"
          >
            {isArchivingBatch ? "Archiving..." : "Archive Batch"}
          </button>
        </div>
      </Modal>
    </MainPageWrapper>
  );
};

export default CampaignBatchList;
