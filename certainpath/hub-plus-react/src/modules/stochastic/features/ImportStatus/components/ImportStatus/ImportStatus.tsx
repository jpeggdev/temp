import React, { useMemo, useState, useCallback } from "react";
import { useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { useImportStatus } from "../../hooks/useImportStatus";
import { downloadCompanyDataImportJobFile } from "@/api/downloadCompanyDataImportJobFile/downloadCompanyDataImportJobFileApi";
import LogStreamModal from "../LogStreamModal/LogStreamModal";
import { createImportStatusColumns } from "../ImportStatusColumns/ImportStatusColumns";
import { CompanyDataImportJob } from "../../graphql/subscriptions/onCompanyDataImportJob/types";
import DataTable from "@/components/Datatable/Datatable";

const ImportStatus: React.FC = () => {
  const userAppSettings = useSelector(
    (state: RootState) => state.userAppSettings.userAppSettings,
  );
  const companyId = userAppSettings?.companyId ?? 0;

  const {
    items,
    loading,
    error,
    pagination,
    handlePaginationChange,
    totalCount,
  } = useImportStatus(companyId);

  const [selectedError, setSelectedError] = useState<string | null>(null);
  const [selectedLogJobId, setSelectedLogJobId] = useState<number | null>(null);

  const handleDownload = useCallback(async (uuid: string) => {
    try {
      const { blob, headers } = await downloadCompanyDataImportJobFile(uuid);
      let filename = `import_${uuid}`;
      const disposition = headers["content-disposition"];
      if (disposition) {
        const match = disposition.match(/filename="([^"]+)"/);
        if (match && match[1]) {
          filename = match[1];
        }
      }
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", filename);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (err) {
      console.error("Download failed:", err);
      alert("Error downloading file.");
    }
  }, []);

  const columns = useMemo(
    () =>
      createImportStatusColumns({
        onErrorClick: (errorMsg) => setSelectedError(errorMsg),
        onDownloadClick: (uuid) => handleDownload(uuid),
      }),
    [handleDownload],
  );

  return (
    <MainPageWrapper
      error={error ? error.message : null}
      title="Import History"
    >
      <DataTable<CompanyDataImportJob>
        columns={columns}
        data={items}
        error={error ? error.message : null}
        loading={loading && items.length === 0}
        noDataMessage="No imports found"
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

      {selectedError && (
        <div className="fixed inset-0 flex items-center justify-center z-50">
          <div
            className="absolute inset-0 bg-black opacity-50"
            onClick={() => setSelectedError(null)}
          />
          <div className="relative bg-white p-6 rounded shadow-lg z-10 max-w-3xl max-h-[80vh] overflow-y-auto">
            <h2 className="text-xl font-bold mb-4">Error Details</h2>
            <pre className="whitespace-pre-wrap break-words text-sm bg-light p-4 rounded">
              {selectedError}
            </pre>
            <div className="mt-4 flex space-x-2">
              <button
                className="px-3 py-1 rounded bg-light hover:bg-dark text-fontColor text-sm"
                onClick={() => {
                  navigator.clipboard.writeText(selectedError ?? "");
                  alert("Error message copied to clipboard!");
                }}
              >
                Copy Error
              </button>
              <button
                className="px-3 py-1 rounded bg-secondary hover:bg-secondary-dark text-white text-sm"
                onClick={() => setSelectedError(null)}
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {selectedLogJobId && (
        <LogStreamModal
          jobId={selectedLogJobId}
          onClose={() => setSelectedLogJobId(null)}
        />
      )}
    </MainPageWrapper>
  );
};

export default ImportStatus;
