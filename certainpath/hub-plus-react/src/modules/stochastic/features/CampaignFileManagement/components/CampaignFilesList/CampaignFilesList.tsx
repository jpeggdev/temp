import React, { useCallback, useMemo } from "react";
import { useParams } from "react-router-dom";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { createCampaignFileColumns } from "../CampaignFileColumns/CampaignFileColumns";
import { CampaignFile } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignFiles/types";
import { useCampaignFiles } from "../../hooks/useCampaignFile";
import { downloadCampaignFile } from "../../../../../../api/downloadCampaignFile/downloadCampaignFileApi";
import DataTable from "@/components/Datatable/Datatable";
import { useCampaignInfo } from "@/modules/stochastic/shared/hooks/useCampaignInfo";

const CampaignFilesList: React.FC = () => {
  const { campaignId } = useParams<{ campaignId: string }>();
  const numericCampaignId = Number(campaignId);

  const {
    campaign,
    loading: loadingCampaign,
    error: campaignError,
  } = useCampaignInfo(numericCampaignId);

  const {
    files,
    totalCount,
    loading: filesLoading,
    error: filesError,
    pagination,
    handlePaginationChange,
  } = useCampaignFiles(numericCampaignId);

  const handleDownload = useCallback(async (file: CampaignFile) => {
    try {
      const requestData = {
        bucketName: file.bucketName,
        objectKey: file.objectKey,
      };
      const fileBlob = await downloadCampaignFile(requestData);

      const url = window.URL.createObjectURL(fileBlob);
      const a = document.createElement("a");
      a.href = url;
      a.download = file.originalFilename || "downloaded-file";
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error("Failed to download file:", error);
      alert("An error occurred while downloading the file.");
    }
  }, []);

  const columns = useMemo(
    () => createCampaignFileColumns({ handleDownload }),
    [handleDownload],
  );

  const combinedLoading = filesLoading || loadingCampaign;
  const combinedError = filesError || campaignError;

  const manualBreadcrumbs = useMemo(() => {
    if (!campaignId) return undefined;
    const campaignName = campaign?.name ?? `Campaign ${campaignId}`;

    return [
      { path: "/stochastic", label: "Stochastic Dashboard" },
      { path: "/stochastic/campaigns", label: "Campaigns" },
      {
        path: `/stochastic/campaigns/${campaignId}/files`,
        label: `${campaignName} Files`,
        clickable: false,
      },
    ];
  }, [campaign, campaignId]);

  return (
    <MainPageWrapper
      error={combinedError}
      manualBreadcrumbs={manualBreadcrumbs}
      title="Campaign Files"
    >
      <DataTable<CampaignFile>
        columns={columns}
        data={files}
        error={combinedError}
        loading={combinedLoading}
        noDataMessage="No campaign files found"
        onPageChange={(newPageIndex, newPageSize) =>
          handlePaginationChange({
            pageIndex: newPageIndex,
            pageSize: newPageSize,
          })
        }
        pageIndex={pagination.pageIndex}
        pageSize={pagination.pageSize}
        rowKeyExtractor={(file) => file.id}
        totalCount={totalCount}
      />
    </MainPageWrapper>
  );
};

export default CampaignFilesList;
