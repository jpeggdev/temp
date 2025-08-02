import { useState } from "react";
import { fetchStochasticClientMailDataCsv } from "@/api/fetchStochasticClientMailDataCsv/fetchStochasticClientMailDataCsvApi";
import { downloadFile } from "@/utils/downloadFile";

export function useDownloadSummaryCSV() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const downloadSummaryCSV = async (week: number, year: number) => {
    setLoading(true);
    setError(null);
    try {
      const blob = await fetchStochasticClientMailDataCsv({
        week,
        year,
        isCsv: true,
      });
      const fileName = `stochastic-summary-week-${week}-year-${year}.csv`;
      downloadFile(blob, fileName);
    } catch (err: unknown) {
      if (err instanceof Error) {
        console.error("Campaign Summary file download error:", err.message);
        setError(err.message);
      } else {
        setError("Failed to download Campaign Summary file");
      }
    } finally {
      setLoading(false);
    }
  };

  return {
    loading,
    downloadSummaryCSV,
    error,
  };
}
