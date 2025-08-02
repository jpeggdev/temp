import React, { useMemo } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Button } from "@/components/Button/Button";
import { useNavigate } from "react-router-dom";
import DataTable from "@/components/Datatable/Datatable";
import { emailCampaignColumns } from "@/modules/emailManagement/features/EmailCampaignManagement/components/EmailCampaignColumns/EmailCampaignColumns";
import { EmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/types";
import { useEmailCampaignList } from "@/modules/emailManagement/features/EmailCampaignManagement/hooks/useEmailCampaignList";
import EmailCampaignListFilters from "@/modules/emailManagement/features/EmailCampaignManagement/components/EmailCampaignListFilters/EmailCampaignListFilters";
import { useDeleteEmailCampaign } from "../../hooks/useDeleteEmailCampaign";
import DeleteEmailCampaignModal from "@/modules/emailManagement/features/EmailCampaignManagement/components/DeleteEmailCampaignModal/DeleteEmailCampaignModal";
import { useDuplicateEmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/hooks/useDuplicateEmailCampaign";
import DuplicateEmailCampaignModal from "@/modules/emailManagement/features/EmailCampaignManagement/components/DuplicateEmailCampaignModal/DuplicateEmailCampaignModal";

const EmailCampaignList: React.FC = () => {
  const navigate = useNavigate();

  const {
    loading,
    error,
    totalCount,
    emailCampaignStatuses,
    filters,
    pagination,
    emailCampaigns,
    refetchEmailCampaigns,
    handleEdit,
    handleFilterChange,
    handlePaginationChange,
  } = useEmailCampaignList();

  const {
    isDeleting,
    handleDelete,
    showDeleteModal,
    handleShowDeleteModal,
    handleCloseDeleteModal,
  } = useDeleteEmailCampaign({ refetchEmailCampaigns });

  const {
    isDuplicating,
    handleDuplicate,
    showDuplicateModal,
    handleShowDuplicateModal,
    handleCloseDuplicateModal,
  } = useDuplicateEmailCampaign({
    refetchEmailCampaigns,
  });

  const columns = useMemo(
    () =>
      emailCampaignColumns({
        handleEdit,
        handleShowDeleteModal,
        handleShowDuplicateModal,
      }),
    [],
  );

  return (
    <>
      <MainPageWrapper
        actions={
          <ShowIfHasAccess requiredRoles={["ROLE_SUPER_ADMIN"]}>
            <Button
              disabled={false}
              onClick={() => {
                navigate("/email-management/email-campaign/new");
              }}
            >
              New Campaign
            </Button>
          </ShowIfHasAccess>
        }
        error={error}
        title="Email Campaigns"
      >
        <EmailCampaignListFilters
          emailCampaignStatuses={emailCampaignStatuses}
          filters={filters}
          onFilterChange={handleFilterChange}
        />

        <div className="relative">
          <DataTable<EmailCampaign>
            columns={columns}
            data={emailCampaigns}
            error={error}
            loading={loading}
            noDataMessage="No email campaigns found"
            onPageChange={(newPageIndex, newPageSize) =>
              handlePaginationChange({
                pageIndex: newPageIndex,
                pageSize: newPageSize,
              })
            }
            pageIndex={pagination.pageIndex}
            pageSize={pagination.pageSize}
            rowKeyExtractor={(item) => item.id}
            totalCount={totalCount}
          />
        </div>
      </MainPageWrapper>

      <DuplicateEmailCampaignModal
        handleDuplicate={handleDuplicate}
        isDuplicating={isDuplicating}
        isOpen={showDuplicateModal}
        onClose={handleCloseDuplicateModal}
      />

      <DeleteEmailCampaignModal
        handleDelete={handleDelete}
        isDeleting={isDeleting}
        isOpen={showDeleteModal}
        onClose={handleCloseDeleteModal}
      />
    </>
  );
};

export default EmailCampaignList;
