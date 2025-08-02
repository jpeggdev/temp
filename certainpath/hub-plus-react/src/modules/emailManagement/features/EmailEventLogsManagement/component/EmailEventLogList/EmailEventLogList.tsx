import React, { useMemo } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import DataTable from "@/components/Datatable/Datatable";
import { emailEventLogsColumns } from "@/modules/emailManagement/features/EmailEventLogsManagement/component/EmailEventLogColumns/EmailEventLogsColumns";
import EmailEventPeriodPicker from "@/modules/emailManagement/features/EmailEventLogsManagement/component/EmailEventPeriodPicker/EmailEventPeriodPicker";
import { useEmailCampaignEventLogs } from "@/modules/emailManagement/features/EmailEventLogsManagement/hooks/useEmailCampaignEventLogs";
import { EmailCampaignEventLog } from "@/modules/emailManagement/features/EmailEventLogsManagement/api/fetchEmailCampaignEventLogs/types";
import EmailCampaignEventLogMetrics from "@/modules/emailManagement/features/EmailEventLogsManagement/component/EmailCampaignEventLogMetrics/EmailCampaignEventLogMetrics";
import EmailEventLogsFilters from "@/modules/emailManagement/features/EmailEventLogsManagement/component/EmailEventLogsFilters/EmailEventLogsFilters";

const EmailEventLogList: React.FC = () => {
  const {
    loading,
    error,
    filters,
    pagination,
    handleFilterChange,
    emailCampaignEventLogs,
    emailCampaignEventLogsMetadata,
    emailCampaignEventLogsTotalCount,
    emailEventPeriod,
    handlePaginationChange,
    setEmailEventPeriod,
    handleSortingChange,
  } = useEmailCampaignEventLogs();

  const columns = useMemo(() => emailEventLogsColumns(), []);

  return (
    <MainPageWrapper
      actions={
        <div className="hidden sm:block">
          <EmailEventPeriodPicker
            selectedEmailEventPeriod={emailEventPeriod}
            setEmailEventPeriod={setEmailEventPeriod}
          />
        </div>
      }
      error={error}
      loading={loading}
      title="Email Activity"
    >
      <div className="relative">
        <div className="space-y-8">
          <div className="block sm:hidden">
            <EmailEventPeriodPicker
              selectedEmailEventPeriod={emailEventPeriod}
              setEmailEventPeriod={setEmailEventPeriod}
            />
          </div>

          <div className="flex flex-wrap gap-4">
            <EmailCampaignEventLogMetrics
              emailCampaignEventLogsMetadata={emailCampaignEventLogsMetadata}
            />
          </div>

          <EmailEventLogsFilters
            filters={filters}
            onFilterChange={handleFilterChange}
          />

          <DataTable<EmailCampaignEventLog>
            columns={columns}
            data={emailCampaignEventLogs}
            error={error}
            noDataMessage="No email event logs found"
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
            totalCount={emailCampaignEventLogsTotalCount}
          />
        </div>
      </div>
    </MainPageWrapper>
  );
};

export default EmailEventLogList;
