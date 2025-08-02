import { useState } from "react";
import { archiveBatch as archive } from "@/api/archiveBatch/archiveBatchApi";
import { useNotification } from "@/context/NotificationContext";

export function useArchiveBatch() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const { showNotification } = useNotification();

  const archiveBatch = async (
    batchId: number,
  ): Promise<{ success: boolean; message?: string }> => {
    setLoading(true);
    setError(null);

    try {
      const response = await archive({ batchId });

      const successMessage =
        response.data.message ||
        `Batch ${batchId} has been archived successfully.`;

      showNotification(
        "Successfully archived batch",
        successMessage,
        "success",
      );

      return { success: true, message: successMessage };
    } catch (err) {
      const errorMessage = `Batch ${batchId} was not archived.`;

      setError(errorMessage);
      showNotification("Failed to archive batch", errorMessage, "error");
      console.error("Failed to archive batch:", err);

      return { success: false, message: errorMessage };
    } finally {
      setLoading(false);
    }
  };

  return { archiveBatch, loading, error };
}
