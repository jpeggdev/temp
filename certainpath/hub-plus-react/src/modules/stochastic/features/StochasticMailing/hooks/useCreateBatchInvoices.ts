import { useCallback, useState } from "react";
import { useNotification } from "@/context/NotificationContext";
import { createBatchInvoices as createBatchInvoicesApi } from "@/api/createBatchInvoices/createBatchInvoicesApi";
import { StochasticClientMailDataRow } from "@/api/fetchStochasticClientMailData/types";
import { CreateBatchInvoiceRequest } from "@/api/createBatchInvoices/types";

export const useCreateBatchInvoices = () => {
  const [loading, setLoading] = useState(false);
  const { showNotification } = useNotification();

  const billSelectedBatches = useCallback(
    async (
      checkedRows: Record<string, boolean>,
      mailDataRows: StochasticClientMailDataRow[],
    ) => {
      const selectedCount = Object.keys(checkedRows).filter(
        (id) => checkedRows[id],
      ).length;

      if (selectedCount === 0) {
        return [];
      }

      setLoading(true);

      try {
        const selectedBatches = mailDataRows.filter(
          (row) => checkedRows[row.id],
        );

        const invoices: CreateBatchInvoiceRequest[] = selectedBatches.map(
          (row) => ({
            accountIdentifier: row.intacctId?.toString() ?? "",
            type: row.campaignProduct?.category ?? "",
            quantityMailed: row.batchPricing?.actualQuantity.toString() ?? "",
            serviceUnitPrice: row.batchPricing?.pricePerPiece.toString() ?? "",
            postageUnitPrice:
              (row.batchPricing?.actualQuantity &&
              row.batchPricing.actualQuantity > 0
                ? (
                    row.batchPricing.postageExpense /
                    row.batchPricing.actualQuantity
                  ).toString()
                : "") ?? "",
            batchReference: row.id.toString() ?? "",
          }),
        );
        const result = await createBatchInvoicesApi(invoices);
        const successMessage = `Successfully billed ${selectedCount} batches.`;
        showNotification("Success", successMessage, "success");
        return result;
      } catch (error) {
        console.error("Failed to bill batches:", error);
        if (error instanceof Error) {
          showNotification(
            "Error",
            error.message || "Failed to bill selected batches.",
            "error",
          );
        } else {
          showNotification("Error", "An unknown error occurred.", "error");
        }
        throw error;
      } finally {
        setLoading(false);
      }
    },
    [showNotification],
  );

  return {
    loading,
    billSelectedBatches,
  };
};
