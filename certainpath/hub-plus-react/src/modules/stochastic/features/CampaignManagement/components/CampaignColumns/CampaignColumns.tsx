import React from "react";
import { Campaign } from "@/api/fetchCompanyCampaigns/types";
import CampaignManagementActionMenu from "../CampaignManagementActionMenu/CampaignManagementActionMenu";
import { Column } from "@/components/Datatable/types";

interface CreateCampaignsColumnsProps {
  handleViewBatches: (id: number) => void;
  handleViewFiles: (id: number) => void;
  handleViewCampaign: (id: number) => void;
}

export function createCampaignsColumns({
  handleViewBatches,
  handleViewFiles,
  handleViewCampaign,
}: CreateCampaignsColumnsProps): Column<Campaign>[] {
  return [
    {
      header: "Campaign ID",
      accessorKey: "id",
      enableSorting: true,
    },
    {
      header: "Campaign Name",
      accessorKey: "name",
      enableSorting: true,
    },
    {
      header: "Phone Number",
      accessorKey: "phoneNumber",
      enableSorting: true,
      cell: ({ row }) => row.original.phoneNumber || "N/A",
    },
    {
      header: "Start Date",
      accessorKey: "startDate",
      enableSorting: true,
      cell: ({ row }) => row.original.startDate || "N/A",
    },
    {
      header: "End Date",
      accessorKey: "endDate",
      enableSorting: true,
      cell: ({ row }) => row.original.endDate || "N/A",
    },
    {
      header: "Status",
      accessorKey: "campaignStatus",
      enableSorting: true,
      cell: ({ row }) =>
        row.original.campaignStatus?.name
          ? row.original.campaignStatus.name.charAt(0).toUpperCase() +
            row.original.campaignStatus.name.slice(1)
          : "N/A",
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => (
        <CampaignManagementActionMenu
          campaignId={row.original.id}
          onViewBatches={handleViewBatches}
          onViewCampaign={handleViewCampaign}
          onViewFiles={handleViewFiles}
        />
      ),
    },
  ];
}
