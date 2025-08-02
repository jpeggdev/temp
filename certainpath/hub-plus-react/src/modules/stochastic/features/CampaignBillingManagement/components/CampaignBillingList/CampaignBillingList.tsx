import React, { useCallback, useState } from "react";
import CampaignBillingListFilters from "@/modules/stochastic/features/CampaignBillingManagement/components/CampaignBillingListFilters/CampaignBillingListFilters";
import DataTable from "../../../../../../components/Datatable/Datatable";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { Campaign } from "@/api/fetchCompanyCampaigns/types";
import { CampaignStatus } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignStatuses/types";
import { createBillingCampaignsColumns } from "../CampaignBillingColumns/CampaignBillingColumns";
import { useCampaigns } from "../../hooks/useCampaigns";

const CampaignBillingList: React.FC = () => {
  const {
    campaigns,
    totalCount,
    loading,
    error,
    pagination,
    handlePaginationChange,
    handleCampaignStatusChange,
    campaignStatusId,
  } = useCampaigns();

  const [campaignStatuses] = useState<CampaignStatus[]>([]);

  const columns = createBillingCampaignsColumns();

  const handleFilterChange = useCallback(
    (newStatusId?: number) => {
      handleCampaignStatusChange(newStatusId);
    },
    [handleCampaignStatusChange],
  );

  return (
    <MainPageWrapper error={error} title="Campaign Billing">
      <CampaignBillingListFilters
        campaignStatusId={campaignStatusId}
        campaignStatuses={campaignStatuses}
        onFilterChange={handleFilterChange}
      />
      <div className="relative">
        <DataTable<Campaign>
          columns={columns}
          data={campaigns}
          error={error}
          loading={loading}
          noDataMessage="No campaigns found"
          onPageChange={(newPageIndex, newPageSize) =>
            handlePaginationChange({
              pageIndex: newPageIndex,
              pageSize: newPageSize,
            })
          }
          pageIndex={pagination.pageIndex}
          pageSize={pagination.pageSize}
          rowKeyExtractor={(campaign) => campaign.id}
          totalCount={totalCount}
        />
      </div>
    </MainPageWrapper>
  );
};

export default CampaignBillingList;
