import { Column } from "@/components/Datatable/types";
import { EmailCampaign } from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/types";
import React from "react";
import EmailCampaignActionMenu from "@/modules/emailManagement/features/EmailCampaignManagement/components/EmailCampaignActionMenu/EmailCampaignActionMenu";
import { Badge } from "@/components/Badge/Badge";

interface emailCampaignColumnsProps {
  handleEdit: (id: number) => void;
  handleShowDeleteModal: (id: number) => void;
  handleShowDuplicateModal: (id: number) => void;
}

export function emailCampaignColumns({
  handleEdit,
  handleShowDeleteModal,
  handleShowDuplicateModal,
}: emailCampaignColumnsProps): Column<EmailCampaign>[] {
  return [
    {
      header: "Name",
      accessorKey: "campaignName",
    },
    {
      header: "Subject Line",
      accessorKey: "emailSubject",
      cell: ({ row }) => {
        return row.original.emailSubject || "N/A";
      },
    },
    {
      header: "Status",
      accessorKey: "emailTemplateCategories",
      cell: ({ row }) => {
        const campaignStatus = row.original.emailCampaignStatus;
        return (
          <Badge className="whitespace-nowrap text-white" variant="default">
            {campaignStatus?.displayName || "Unnamed"}
          </Badge>
        );
      },
    },
    {
      header: "Recipients",
      accessorKey: "recipientCount",
    },
    {
      header: "Date Sent",
      accessorKey: "dateSent",
      cell: ({ row }) =>
        row.original.dateSent
          ? new Date(row.original.dateSent).toLocaleDateString()
          : "Not Sent Yet",
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const emailCampaign = row.original;
        const emailCampaignStatus = emailCampaign.emailCampaignStatus;

        return (
          <EmailCampaignActionMenu
            emailCampaignId={emailCampaign.id}
            isReadOnly={emailCampaignStatus.name !== "draft"}
            onDeleteEmailCampaign={handleShowDeleteModal}
            onDuplicateEmailCampaign={handleShowDuplicateModal}
            onEditEmailCampaign={handleEdit}
          />
        );
      },
    },
  ];
}
