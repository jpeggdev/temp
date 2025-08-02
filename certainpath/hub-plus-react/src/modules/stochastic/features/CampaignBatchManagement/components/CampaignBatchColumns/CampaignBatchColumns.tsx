import React from "react";
import { Batch } from "@/api/getCampaignBatches/types";
import CampaignBatchActionMenu from "../CampaignBatchActionMenu/CampaignBatchActionMenu";
import { Column } from "@/components/Datatable/types";

interface CreateCampaignBatchesColumnsProps {
  handleViewProspects: (id: number) => void;
  handleDownloadCsv: (id: number) => void;
  handleOpenArchiveBatchModal: (id: number) => void;
}

export function createCampaignBatchesColumns({
  handleViewProspects,
  handleDownloadCsv,
  handleOpenArchiveBatchModal,
}: CreateCampaignBatchesColumnsProps): Column<Batch>[] {
  return [
    {
      header: "Batch ID",
      enableSorting: true,
      accessorKey: "id",
      cell: ({ row }) => (
        <div
          className="cursor-pointer hover:text-blue-600"
          onClick={() => handleViewProspects(row.original.id)}
        >
          {row.original.id}
        </div>
      ),
    },
    {
      header: "Week",
      accessorKey: "week_number",
      enableSorting: true,
      cell: ({ row }) => (
        <div
          className="cursor-pointer hover:text-blue-600"
          onClick={() => handleViewProspects(row.original.id)}
        >
          {row.original.campaignIterationWeek?.week_number ?? "N/A"}
        </div>
      ),
    },
    {
      header: "Batch Status",
      accessorKey: "batchStatus",
      enableSorting: true,
      cell: ({ row }) => {
        const name = row.original.batchStatus?.name;
        if (!name)
          return (
            <div
              className="cursor-pointer hover:text-blue-600"
              onClick={() => handleViewProspects(row.original.id)}
            >
              N/A
            </div>
          );
        // Capitalize first letter
        const capitalized = name.charAt(0).toUpperCase() + name.slice(1);
        return (
          <div
            className="cursor-pointer hover:text-blue-600"
            onClick={() => handleViewProspects(row.original.id)}
          >
            {capitalized}
          </div>
        );
      },
    },
    {
      header: "Prospects Count",
      accessorKey: "prospectsCount",
      enableSorting: true,
      cell: ({ row }) => (
        <div
          className="cursor-pointer hover:text-blue-600"
          onClick={() => handleViewProspects(row.original.id)}
        >
          {row.original.prospectsCount ?? "N/A"}
        </div>
      ),
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => (
        <CampaignBatchActionMenu
          batch={row.original}
          onDownloadCsv={handleDownloadCsv}
          onOpenArchiveBatchModal={handleOpenArchiveBatchModal}
          onViewProspects={handleViewProspects}
        />
      ),
    },
  ];
}
