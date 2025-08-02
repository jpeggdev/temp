import React from "react";
import { ResourceItem } from "../../slices/resourceListSlice";
import ResourceActionMenu from "../ResourceActionMenu/ResourceActionMenu";
import { Switch } from "@/components/ui/switch";
import { Column } from "@/components/Datatable/types";

interface CreateResourceColumnsProps {
  onTogglePublished: (uuid: string, newVal: boolean) => void;
  onToggleFeatured: (uuid: string, newVal: boolean) => void;
  onEditResource: (uuid: string) => void;
  onDeleteResource: (uuid: string) => void;
}

export function createResourceColumns({
  onTogglePublished,
  onToggleFeatured,
  onEditResource,
  onDeleteResource,
}: CreateResourceColumnsProps): Column<ResourceItem>[] {
  return [
    {
      header: "Title",
      accessorKey: "title",
      enableSorting: true,
    },
    {
      header: "Type",
      accessorKey: "resourceType",
      cell: ({ row }) => row.original.resourceType || "—",
    },
    {
      header: "Status",
      accessorKey: "isPublished",
      enableSorting: true,
      cell: ({ row }) => {
        const isPublished = row.original.isPublished;
        const uuid = row.original.uuid;

        const handleChange = (checked: boolean) => {
          onTogglePublished(uuid, checked);
        };

        const stopPropagation = (e: React.MouseEvent) => {
          e.stopPropagation();
        };

        return (
          <Switch
            checked={isPublished}
            onCheckedChange={handleChange}
            onClick={stopPropagation}
          />
        );
      },
    },
    {
      header: "Featured",
      accessorKey: "isFeatured",
      enableSorting: true,
      cell: ({ row }) => {
        const isFeatured = row.original.isFeatured;
        const uuid = row.original.uuid;

        const handleChange = (checked: boolean) => {
          onToggleFeatured(uuid, checked);
        };

        const stopPropagation = (e: React.MouseEvent) => {
          e.stopPropagation();
        };

        return (
          <Switch
            checked={isFeatured}
            onCheckedChange={handleChange}
            onClick={stopPropagation}
          />
        );
      },
    },
    {
      header: "Created At",
      accessorKey: "createdAt",
      enableSorting: true,
      cell: ({ row }) => {
        const val = row.original.createdAt;
        return val ? new Date(val).toLocaleDateString() : "—";
      },
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => (
        <ResourceActionMenu
          onDeleteResource={onDeleteResource}
          onEditResource={onEditResource}
          resourceUuid={row.original.uuid}
        />
      ),
    },
  ];
}
