import React from "react";
import { Column } from "@/components/Datatable/types";
import CertainPathCheckbox from "@/components/CertainPathCheckbox/CertainPathCheckbox";
import { StochasticClientMailDataRow } from "@/api/fetchStochasticClientMailData/types";

const renderCell = (value: React.ReactNode): JSX.Element => (
  <div className="cursor-pointer hover:text-blue-600 text-left">{value}</div>
);

const formatCurrency = (value: number | undefined | null): string => {
  return "$" + (value ?? 0).toFixed(2);
};

export function createStochasticMailingColumns(
  hasProcessedStatus: boolean = false,
  checkedRows: Record<string, boolean> = {},
  onCheckboxChange: (rowId: string, checked: boolean) => void,
  isAllSelected: boolean = false,
  onSelectAllChange: (checked: boolean) => void,
): Column<StochasticClientMailDataRow>[] {
  const baseColumns: Column<StochasticClientMailDataRow>[] = [
    {
      header: "Intacct ID",
      accessorKey: "intacctId",
      enableSorting: true,
      cell: ({ row }) => renderCell(row.original.intacctId || "N/A"),
    },
    {
      header: "Client Name",
      accessorKey: "clientName",
      enableSorting: true,
      cell: ({ row }) => renderCell(row.original.clientName || "N/A"),
    },
    {
      header: "Campaign ID",
      accessorKey: "campaign_id",
      enableSorting: true,
      cell: ({ row }) => renderCell(row.original.campaignId || "N/A"),
    },
    {
      header: "Campaign Name",
      accessorKey: "campaign_name",
      enableSorting: true,
      cell: ({ row }) => renderCell(row.original.campaignName || "N/A"),
    },
    {
      header: "Job Number",
      accessorKey: "batch_number",
      enableSorting: true,
      cell: ({ row }) => renderCell(row.original.batchNumber),
    },
    {
      header: "Status",
      accessorKey: "batch_status",
      enableSorting: true,
      cell: ({ row }) => renderCell(row.original.batchStatus || "N/A"),
    },
    {
      header: "Projected/Actual Qty",
      accessorKey: "projected_qty",
      enableSorting: true,
      cell: ({ row }) => {
        const projectedQty = (row.original.prospectCount || 0).toString();
        const actualQty = row.original.batchPricing?.actualQuantity;
        const displayValue = actualQty
          ? `${projectedQty}/${actualQty}`
          : projectedQty;

        return renderCell(displayValue);
      },
    },
  ];

  const processedColumns: Column<StochasticClientMailDataRow>[] =
    hasProcessedStatus
      ? [
          {
            header: "Material/Postage Cost",
            accessorKey: "combined_cost",
            enableSorting: true,
            cell: ({ row }) => {
              const materialCost = formatCurrency(
                row.original.batchPricing?.materialExpense,
              );
              const postageCost = formatCurrency(
                row.original.batchPricing?.postageExpense,
              );
              return renderCell(`${materialCost}/${postageCost}`);
            },
          },
          {
            header: "Total Cost",
            accessorKey: "total_cost",
            enableSorting: true,
            cell: ({ row }) =>
              renderCell(
                formatCurrency(row.original.batchPricing?.totalExpense),
              ),
          },
        ]
      : [];

  const actionColumn: Column<StochasticClientMailDataRow>[] = hasProcessedStatus
    ? [
        {
          header: () => (
            <div className="flex items-center justify-center">
              <CertainPathCheckbox
                checked={isAllSelected}
                label=""
                name="select-all-checkbox"
                onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                  onSelectAllChange(e.target.checked)
                }
              />
            </div>
          ),
          accessorKey: "select_checkbox",
          enableSorting: false,
          cell: ({ row }) => {
            const { batchStatus, batchPricing, id } = row.original;

            if (
              batchStatus === "processed" &&
              batchPricing?.actualQuantity != null
            ) {
              const rowId = id.toString();
              return (
                <div className="flex items-center justify-center space-x-2">
                  <CertainPathCheckbox
                    checked={checkedRows[rowId] || false}
                    label=""
                    name={`checkbox-${rowId}`}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                      onCheckboxChange(rowId, e.target.checked)
                    }
                  />
                </div>
              );
            }
            return null;
          },
        },
      ]
    : [];

  return [...baseColumns, ...processedColumns, ...actionColumn];
}
