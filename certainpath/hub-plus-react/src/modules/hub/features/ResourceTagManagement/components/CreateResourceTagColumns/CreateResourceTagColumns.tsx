import React from "react";
import { Column } from "@/components/Datatable/types";
import ResourceTagActionMenu from "../ResourceTagActionMenu/ResourceTagActionMenu";

export interface ResourceTagItem {
  id: number;
  name: string;
}

interface CreateResourceTagColumnsProps {
  onEditTag: (id: number) => void;
  onDeleteTag: (id: number) => void;
}

export function createResourceTagColumns({
  onEditTag,
  onDeleteTag,
}: CreateResourceTagColumnsProps): Column<ResourceTagItem>[] {
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
        const tagId = row.original.id;

        return (
          <ResourceTagActionMenu
            onDeleteTag={onDeleteTag}
            onEditTag={onEditTag}
            tagId={tagId}
          />
        );
      },
    },
  ];
}
