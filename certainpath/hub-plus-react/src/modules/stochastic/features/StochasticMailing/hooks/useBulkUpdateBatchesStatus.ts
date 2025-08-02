import { useState, useCallback } from "react";
import { bulkUpdateBatchesStatus as bulkUpdate } from "@/api/bulkUpdateBatchesStatus/bulkUpdateBatchesStatusApi";

export const useBulkUpdateBatchesStatus = () => {
  const [loading, setLoading] = useState(false);

  const bulkUpdateBatchesStatus = useCallback(
    async (year: number, week: number, newStatus: string) => {
      setLoading(true);
      try {
        await bulkUpdate({ year, week, status: newStatus });
      } catch (error) {
        console.error("Failed to update batch status:", error);
        throw error;
      } finally {
        setLoading(false);
      }
    },
    [],
  );

  return {
    loading,
    bulkUpdateBatchesStatus,
  };
};
