import React, { useCallback, useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useCampaigns } from "../../hooks/useCampaigns";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import DataTable from "../../../../../../components/Datatable/Datatable";
import { createCampaignsColumns } from "../CampaignColumns/CampaignColumns";
import { Button } from "@/components/Button/Button";
import { CampaignStatus } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignStatuses/types";
import { fetchCampaignStatuses } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignStatuses/fetchCampaignStatusesApi";
import CampaignListFilters from "@/modules/stochastic/features/CampaignManagement/components/CampaignListFilters/CampaignListFilters";
import { useSelector } from "react-redux";
import { useSubscription } from "@apollo/client";
import { RootState } from "@/app/rootReducer";
import { ON_COMPANY_DATA_IMPORT_JOB_IN_PROGRESS_COUNT_SUBSCRIPTION } from "@/modules/stochastic/features/CampaignManagement/graphql/subscriptions/onCompanyDataImportJobInProgressCount";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Campaign } from "@/api/fetchCompanyCampaigns/types";

const CampaignList: React.FC = () => {
  const navigate = useNavigate();
  const userAppSettings = useSelector(
    (state: RootState) => state.userAppSettings.userAppSettings,
  );

  const companyId = userAppSettings?.companyId ?? 0;
  const { data: importData } = useSubscription(
    ON_COMPANY_DATA_IMPORT_JOB_IN_PROGRESS_COUNT_SUBSCRIPTION,
    {
      variables: { companyId },
      skip: !companyId,
    },
  );

  const inProgressCount =
    importData?.company_data_import_job_aggregate?.aggregate?.count ?? 0;
  const isImportInProgress = inProgressCount > 0;

  const {
    campaigns,
    totalCount,
    loading,
    error,
    pagination,
    handlePaginationChange,
    handleCampaignStatusChange,
    campaignStatusId,
    sorting,
    handleSortingChange,
  } = useCampaigns();

  const [campaignStatuses, setCampaignStatuses] = useState<CampaignStatus[]>(
    [],
  );

  const handleViewBatches = useCallback(
    (id: number) => {
      navigate(`/stochastic/campaigns/${id}/batches`);
    },
    [navigate],
  );

  const handleViewFiles = useCallback(
    (id: number) => {
      navigate(`/stochastic/campaigns/${id}/files`);
    },
    [navigate],
  );

  const handleViewCampaign = useCallback(
    (id: number) => {
      navigate(`/stochastic/campaigns/${id}/view`);
    },
    [navigate],
  );

  const columns = useMemo(
    () =>
      createCampaignsColumns({
        handleViewBatches,
        handleViewFiles,
        handleViewCampaign,
      }),
    [handleViewBatches, handleViewFiles, handleViewCampaign],
  );

  useEffect(() => {
    const loadStatuses = async () => {
      try {
        const response = await fetchCampaignStatuses();
        setCampaignStatuses(response.data);
      } catch (err) {
        console.error("Failed to fetch campaign statuses", err);
      }
    };
    loadStatuses();
  }, []);

  const handleFilterChange = useCallback(
    (newStatusId?: number) => {
      handleCampaignStatusChange(newStatusId);
    },
    [handleCampaignStatusChange],
  );

  return (
    <>
      <MainPageWrapper
        actions={
          <ShowIfHasAccess requiredPermissions={["CAN_CREATE_CAMPAIGNS"]}>
            <Button
              disabled={isImportInProgress}
              onClick={() => navigate("/stochastic/campaigns/new")}
            >
              Create Campaign
            </Button>
          </ShowIfHasAccess>
        }
        error={error}
        title="Campaigns"
      >
        {isImportInProgress && (
          <div className="mb-4 border-l-4 border-yellow-400 bg-yellow-50 p-4">
            <p className="text-sm text-yellow-800">
              Campaign creation is disabled while company data is still being
              imported.
            </p>
          </div>
        )}

        <CampaignListFilters
          campaignStatusId={campaignStatusId}
          campaignStatuses={campaignStatuses}
          onFilterChange={handleFilterChange}
        />

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
          onSortingChange={handleSortingChange}
          pageIndex={pagination.pageIndex}
          pageSize={pagination.pageSize}
          rowKeyExtractor={(campaign) => campaign.id}
          sorting={sorting}
          totalCount={totalCount}
        />
      </MainPageWrapper>
    </>
  );
};

export default CampaignList;
