import React from "react";
import { CampaignFile } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignFiles/types";
import CampaignFileActionMenu from "../CampaignFileActionMenu/CampaignFileActionMenu";
import { Column } from "@/components/Datatable/types";

interface CreateCampaignFileColumnsProps {
  handleDownload: (file: CampaignFile) => void;
}

export function createCampaignFileColumns({
  handleDownload,
}: CreateCampaignFileColumnsProps): Column<CampaignFile>[] {
  return [
    {
      header: "Original Filename",
      accessorKey: "originalFilename",
    },
    {
      header: "Content Type",
      accessorKey: "contentType",
    },
    {
      header: "Created At",
      accessorKey: "createdAt",
      cell: ({ row }) => {
        const createdAt = row.original.createdAt;
        return createdAt ? new Date(createdAt).toLocaleString() : "N/A";
      },
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => (
        <CampaignFileActionMenu
          file={row.original}
          onDownload={handleDownload}
        />
      ),
    },
  ];
}
