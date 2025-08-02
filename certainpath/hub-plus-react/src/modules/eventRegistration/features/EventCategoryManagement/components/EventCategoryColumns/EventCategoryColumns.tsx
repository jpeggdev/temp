import React from "react";
import { Column } from "@/components/Datatable/types";
import EventCategoryActionMenu from "../EventCategoryActionMenu/EventCategoryActionMenu";

export interface EventCategoryItem {
  id: number;
  name: string;
  description?: string | null;
  isActive?: boolean;
}

interface EventCategoryColumnsProps {
  onEditCategory: (id: number) => void;
  onDeleteCategory: (id: number) => void;
}

export function eventCategoryColumns({
  onEditCategory,
  onDeleteCategory,
}: EventCategoryColumnsProps): Column<EventCategoryItem>[] {
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
      header: "Active?",
      accessorKey: "isActive",
      enableSorting: true,
      cell: ({ row }) => (row.original.isActive ? "Yes" : "No"),
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const categoryId = row.original.id;
        return (
          <EventCategoryActionMenu
            categoryId={categoryId}
            onDeleteCategory={onDeleteCategory}
            onEditCategory={onEditCategory}
          />
        );
      },
    },
  ];
}
