import React from "react";
import { Column } from "@/components/Datatable/types";
import EmailTemplateCategoryActionMenu from "../EmailTemplateCategoryActionMenu/EmailTemplateCategoryActionMenu";

export interface EmailTemplateCategoryItem {
  id: number;
  name: string;
  displayedName: string;
  color: {
    id: number;
    value: string;
  };
}

interface CreateEmailTemplateCategoryColumnsProps {
  onEditCategory: (id: number) => void;
  onDeleteCategory: (id: number) => void;
}

export function createEmailTemplateCategoryColumns({
  onEditCategory,
  onDeleteCategory,
}: CreateEmailTemplateCategoryColumnsProps): Column<EmailTemplateCategoryItem>[] {
  return [
    {
      header: "ID",
      accessorKey: "id",
      enableSorting: true,
      cell: ({ row }) => row.original.id,
    },
    {
      header: "Name",
      accessorKey: "name",
      enableSorting: true,
      cell: ({ row }) => row.original.name || "—",
    },
    {
      header: "Displayed Name",
      accessorKey: "displayedName",
      enableSorting: true,
      cell: ({ row }) => row.original.displayedName || "—",
    },
    {
      header: "Color",
      accessorKey: "color.value",
      enableSorting: false,
      cell: ({ row }) => {
        const colorHex = row.original.color?.value;
        if (!colorHex) {
          return "—";
        }
        return (
          <div className="flex items-center gap-2">
            <span
              className="inline-block w-3 h-3 rounded-full"
              style={{ backgroundColor: colorHex }}
            />
            <span>{colorHex}</span>
          </div>
        );
      },
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const categoryId = row.original.id;
        return (
          <EmailTemplateCategoryActionMenu
            categoryId={categoryId}
            onDeleteCategory={onDeleteCategory}
            onEditCategory={onEditCategory}
          />
        );
      },
    },
  ];
}
