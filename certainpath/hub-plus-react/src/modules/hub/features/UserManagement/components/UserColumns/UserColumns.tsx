import React from "react";
import { User } from "../../slices/usersSlice";
import UserManagementActionMenu from "../UserManagementActionMenu/UserManagementActionMenu";
import { Column } from "@/components/Datatable/types";

interface CreateUsersColumnsProps {
  handleEdit: (uuid: string) => void;
  handleImpersonateUser: (uuid: string) => void;
}

export function createUsersColumns({
  handleEdit,
  handleImpersonateUser,
}: CreateUsersColumnsProps): Column<User>[] {
  return [
    {
      header: "ID",
      accessorKey: "id",
    },
    {
      header: "First Name",
      accessorKey: "firstName",
      enableSorting: true,
    },
    {
      header: "Last Name",
      accessorKey: "lastName",
      enableSorting: true,
    },
    {
      header: "Email",
      accessorKey: "email",
      enableSorting: true,
    },
    {
      header: "Salesforce ID",
      accessorKey: "salesforceId",
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const userItem = row.original;
        return (
          <UserManagementActionMenu
            employeeUuid={userItem.employeeUuid || ""}
            onEdit={handleEdit}
            onImpersonate={handleImpersonateUser}
            userUuid={userItem.uuid || ""}
          />
        );
      },
    },
  ];
}
