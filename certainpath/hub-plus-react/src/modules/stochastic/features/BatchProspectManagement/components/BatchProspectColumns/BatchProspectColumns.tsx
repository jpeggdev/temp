import { Column } from "@/components/Datatable/types";
import { Prospect } from "../../../../../../api/getBatchProspects/types";

export function createBatchProspectsColumns(): Column<Prospect>[] {
  return [
    {
      header: "Prospect ID",
      accessorKey: "id",
      enableSorting: true,
    },
    {
      header: "Full Name",
      accessorKey: "fullName",
      enableSorting: true,
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
      header: "Address 1",
      accessorKey: "address1",
      cell: ({ row }) => row.original.address1 || "N/A",
    },
    {
      header: "Address 2",
      accessorKey: "address2",
      cell: ({ row }) => row.original.address2 || "N/A",
    },
    {
      header: "City",
      accessorKey: "city",
      cell: ({ row }) => row.original.city || "N/A",
    },
    {
      header: "State",
      accessorKey: "state",
      cell: ({ row }) => row.original.state || "N/A",
    },
    {
      header: "Postal Code",
      accessorKey: "postalCode",
      cell: ({ row }) => row.original.postalCode || "N/A",
    },
    {
      header: "Do Not Mail",
      accessorKey: "doNotMail",
      cell: ({ row }) => (row.original.doNotMail ? "Yes" : "No"),
    },
    {
      header: "Do Not Contact",
      accessorKey: "doNotContact",
      cell: ({ row }) => (row.original.doNotContact ? "Yes" : "No"),
    },
    {
      header: "External ID",
      accessorKey: "externalId",
      cell: ({ row }) => row.original.externalId || "N/A",
    },
    {
      header: "Preferred",
      accessorKey: "isPreferred",
      cell: ({ row }) => (row.original.isPreferred ? "Yes" : "No"),
    },
    {
      header: "Active",
      accessorKey: "isActive",
      cell: ({ row }) => (row.original.isActive ? "Yes" : "No"),
    },
    {
      header: "Deleted",
      accessorKey: "isDeleted",
      cell: ({ row }) => (row.original.isDeleted ? "Yes" : "No"),
    },
    {
      header: "Company ID",
      accessorKey: "companyId",
      cell: ({ row }) => row.original.companyId || "N/A",
    },
    {
      header: "Customer ID",
      accessorKey: "customerId",
      cell: ({ row }) => row.original.customerId || "N/A",
    },
  ];
}
