import React from "react";
import { Column } from "@/components/Datatable/types";
import EmployeeRoleActionMenu from "../EmployeeRoleActionMenu/EmployeeRoleActionMenu";

export interface EmployeeRoleItem {
  id: number;
  name: string;
}

interface CreateEmployeeRoleColumnsProps {
  onEditRole: (id: number) => void;
  onDeleteRole: (id: number) => void;
}

export function createEmployeeRoleColumns({
  onEditRole,
  onDeleteRole,
}: CreateEmployeeRoleColumnsProps): Column<EmployeeRoleItem>[] {
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
        const roleId = row.original.id;
        return (
          <EmployeeRoleActionMenu
            onDeleteRole={onDeleteRole}
            onEditRole={onEditRole}
            roleId={roleId}
          />
        );
      },
    },
  ];
}
