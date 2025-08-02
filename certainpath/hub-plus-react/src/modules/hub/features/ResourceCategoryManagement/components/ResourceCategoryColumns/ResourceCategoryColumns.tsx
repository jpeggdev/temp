import React from "react";
import { Column } from "@/components/Datatable/types";
import ResourceCategoryActionMenu from "../ResourceCategoryActionMenu/ResourceCategoryActionMenu";

export interface ResourceCategoryItem {
  id: number;
  name: string;
}

interface CreateResourceCategoryColumnsProps {
  onEditCategory: (id: number) => void;
  onDeleteCategory: (id: number) => void;
}

export function createResourceCategoryColumns({
  onEditCategory,
  onDeleteCategory,
}: CreateResourceCategoryColumnsProps): Column<ResourceCategoryItem>[] {
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
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const categoryId = row.original.id;

        return (
          <ResourceCategoryActionMenu
            categoryId={categoryId}
            onDeleteCategory={onDeleteCategory}
            onEditCategory={onEditCategory}
          />
        );
      },
    },
  ];
}
