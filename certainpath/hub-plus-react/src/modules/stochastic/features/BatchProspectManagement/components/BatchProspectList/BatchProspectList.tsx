import React, { useMemo } from "react";
import { useParams } from "react-router-dom";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { useBatchProspects } from "../../hooks/useBatchProspects";
import { createBatchProspectsColumns } from "../BatchProspectColumns/BatchProspectColumns";
import { Prospect } from "../../../../../../api/getBatchProspects/types";
import DataTable from "@/components/Datatable/Datatable";
import { useCampaignInfo } from "@/modules/stochastic/shared/hooks/useCampaignInfo";

const BatchProspectList: React.FC = () => {
  const { campaignId, batchId } = useParams<{
    campaignId: string;
    batchId: string;
  }>();

  const numericCampaignId = useMemo(() => Number(campaignId), [campaignId]);
  const numericBatchId = useMemo(() => Number(batchId), [batchId]);

  const {
    prospects,
    totalCount,
    loading,
    error,
    sorting,
    pagination,
    handlePaginationChange,
    handleSortingChange,
  } = useBatchProspects(numericBatchId);

  const { campaign, error: errorCampaign } = useCampaignInfo(numericCampaignId);

  const columns = useMemo(() => createBatchProspectsColumns(), []);

  const manualBreadcrumbs = useMemo(() => {
    if (!campaignId || !batchId) return undefined;
    const displayName = campaign?.name ?? `Campaign ${campaignId}`;

    return [
      { path: "/stochastic", label: "Stochastic Dashboard" },
      { path: "/stochastic/campaigns", label: "Campaigns" },
      {
        path: `/stochastic/campaigns/${campaignId}/batches`,
        label: `${displayName} Batches`,
      },
      {
        path: `/stochastic/campaigns/${campaignId}/batches/${batchId}/prospects`,
        label: `Batch ${batchId} Prospects`,
        clickable: false,
      },
    ];
  }, [campaignId, batchId, campaign]);

  const combinedError = error || errorCampaign;

  return (
    <MainPageWrapper
      error={combinedError}
      manualBreadcrumbs={manualBreadcrumbs}
      title="Batch Prospects"
    >
      <DataTable<Prospect>
        columns={columns}
        data={prospects}
        error={error}
        loading={loading}
        noDataMessage="No prospects found"
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
    </MainPageWrapper>
  );
};

export default BatchProspectList;
