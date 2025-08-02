import { useState } from "react";
import { fetchBatchProspectsCsv } from "../../../../../api/fetchBatchProspectsCsv/fetchBatchProspectsCsvApi";
import { downloadFile } from "../../../../../utils/downloadFile";

export function useDownloadBatchProspectsCsv() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const downloadCsv = async (batchId: number) => {
    setLoading(true);
    setError(null);
    try {
      const blob = await fetchBatchProspectsCsv(batchId);
      const fileName = `batch_prospects_${batchId}_${new Date().toISOString()}.csv`;
      downloadFile(blob, fileName);
    } catch (err: unknown) {
      if (err instanceof Error) {
        console.error("CSV download error:", err.message);
        setError(err.message);
      } else {
        setError("Failed to download CSV file");
      }
    } finally {
      setLoading(false);
    }
  };

  return {
    downloadCsv,
    loading,
    error,
  };
}
