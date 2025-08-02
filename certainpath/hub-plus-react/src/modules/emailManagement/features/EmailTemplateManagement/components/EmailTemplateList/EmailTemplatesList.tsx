import React, { useMemo } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Button } from "@/components/Button/Button";
import { useNavigate } from "react-router-dom";
import DataTable from "@/components/Datatable/Datatable";
import { useEmailTemplates } from "@/modules/emailManagement/features/EmailTemplateManagement/hooks/useEmailTemplates";
import { createEmailTemplateColumns } from "@/modules/emailManagement/features/EmailTemplateManagement/components/EmailTemplateColumns/EmailTemplateColumns";
import EmailTemplateListFilters from "@/modules/emailManagement/features/EmailTemplateManagement/components/EmailTemplateListFilters/EmailTemplateListFilters";
import { EmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplate/types";

const EmailTemplatesList: React.FC = () => {
  const navigate = useNavigate();

  const {
    emailTemplates,
    loading,
    error,
    totalCount,
    filters,
    pagination,
    handleEditEmailTemplate,
    handleDuplicateEmailTemplate,
    handleDeleteEmailTemplate,
    handleFilterChange,
    handlePaginationChange,
  } = useEmailTemplates();

  const columns = useMemo(
    () =>
      createEmailTemplateColumns({
        handleEditEmailTemplate,
        handleDuplicateEmailTemplate,
        handleDeleteEmailTemplate,
      }),
    [],
  );

  return (
    <MainPageWrapper
      actions={
        <ShowIfHasAccess requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <Button
            disabled={false}
            onClick={() => {
              navigate("/email-management/email-template/new");
            }}
          >
            New Template
          </Button>
        </ShowIfHasAccess>
      }
      error={error}
      title="Email Templates"
    >
      <EmailTemplateListFilters
        filters={filters}
        onFilterChange={handleFilterChange}
      />

      <div className="relative">
        <DataTable<EmailTemplate>
          columns={columns}
          data={emailTemplates}
          error={error}
          loading={loading}
          noDataMessage="No email templates found"
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
  );
};

export default EmailTemplatesList;
