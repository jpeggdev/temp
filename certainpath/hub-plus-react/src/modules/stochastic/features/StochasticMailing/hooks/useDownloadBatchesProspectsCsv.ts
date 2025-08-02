import { useState } from "react";
import { downloadFile } from "@/utils/downloadFile";
import { fetchBatchesProspectsCsv } from "@/api/fetchBatchesProspectsCsv/fetchBatchProspectsCsvApi";

export function useDownloadBatchesProspectsCsv() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const downloadCsv = async (week: number, year: number) => {
    setLoading(true);
    setError(null);

    try {
      const blob = await fetchBatchesProspectsCsv({ week, year });
      const fileName = `week_${week}_year_${year}_stochastic.csv`;
      downloadFile(blob, fileName);
    } catch (err: unknown) {
      if (err instanceof Error) {
        console.error("CLI file download error:", err.message);
        setError(err.message);
      } else {
        setError("Failed to download CLI file");
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
