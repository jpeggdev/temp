import { RestrictedAddress } from "@/api/fetchRestrictedAddresses/types";
import React from "react";
import DoNotMailActionMenu from "../DoNotMailActionMenu/DoNotMailActionMenu";
import { Column } from "@/components/Datatable/types";

interface CreateDoNotMailColumnsProps {
  handleEditAddress: (id: number) => void;
  handleDeleteAddress: (id: number) => void;
}

export function createDoNotMailColumns({
  handleEditAddress,
  handleDeleteAddress,
}: CreateDoNotMailColumnsProps): Column<RestrictedAddress>[] {
  return [
    {
      header: "ID",
      accessorKey: "id",
      enableSorting: true,
    },
    {
      header: "Address 1",
      accessorKey: "address1",
      cell: ({ row }) => row.original.address1 || "N/A",
    },
    {
      header: "City",
      accessorKey: "city",
      cell: ({ row }) => row.original.city || "N/A",
    },
    {
      header: "State",
      accessorKey: "stateCode",
      cell: ({ row }) => row.original.stateCode || "N/A",
    },
    {
      header: "Postal Code",
      accessorKey: "postalCode",
      cell: ({ row }) => {
        const pc = row.original.postalCode || "N/A";
        if (pc === "N/A") return pc;
        if (pc.length <= 5) return pc;
        return pc.slice(0, 5) + "-" + pc.slice(5);
      },
    },
    {
      header: "Is Verified",
      accessorKey: "isVerified",
      cell: ({ row }) => (row.original.isVerified ? "Yes" : "No"),
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => (
        <DoNotMailActionMenu
          addressId={row.original.id}
          onDelete={handleDeleteAddress}
          onEdit={handleEditAddress}
        />
      ),
    },
  ];
}
