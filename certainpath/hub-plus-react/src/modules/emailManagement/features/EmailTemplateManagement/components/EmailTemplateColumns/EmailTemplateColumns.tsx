import React from "react";
import { Badge } from "@/components/Badge/Badge";
import EmailTemplateActionMenu from "@/modules/emailManagement/features/EmailTemplateManagement/components/EmailTemplateActionMenu/EmailTemplateActionMenu";
import { EmailTemplate } from "@/modules/emailManagement/features/EmailTemplateManagement/api/fetchEmailTemplate/types";
import { Column } from "@/components/Datatable/types";

interface CreateEmailTemplateColumnsProps {
  handleEditEmailTemplate: (id: number) => void;
  handleDuplicateEmailTemplate: (id: number) => void;
  handleDeleteEmailTemplate: (id: number) => void;
}

export function createEmailTemplateColumns({
  handleEditEmailTemplate,
  handleDuplicateEmailTemplate,
  handleDeleteEmailTemplate,
}: CreateEmailTemplateColumnsProps): Column<EmailTemplate>[] {
  return [
    {
      header: "Name",
      accessorKey: "templateName",
    },
    {
      header: "Subject",
      accessorKey: "emailSubject",
    },
    {
      header: "Category",
      accessorKey: "emailTemplateCategories",
      cell: ({ row }) => {
        const categories = row.original.emailTemplateCategories;

        if (Array.isArray(categories) && categories.length > 0) {
          return (
            <div className="max-w-[250px] flex gap-2 flex-wrap items-start">
              {categories.map((category, index) => (
                <Badge
                  className="whitespace-nowrap text-white"
                  key={index}
                  style={{
                    backgroundColor: category?.color?.value || "#6B7280",
                  }}
                  variant="secondary"
                >
                  {category?.displayedName || "Unnamed"}
                </Badge>
              ))}
            </div>
          );
        } else {
          return "N/A";
        }
      },
    },
    {
      header: "Created At",
      accessorKey: "createdAt",
      cell: ({ row }) =>
        row.original.createdAt
          ? new Date(row.original.createdAt).toLocaleDateString()
          : "N/A",
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => (
        <EmailTemplateActionMenu
          emailTemplateId={row.original.id}
          onDeleteEmailTemplate={handleDeleteEmailTemplate}
          onDuplicateEmailTemplate={handleDuplicateEmailTemplate}
          onEditEmailTemplate={handleEditEmailTemplate}
        />
      ),
    },
  ];
}
