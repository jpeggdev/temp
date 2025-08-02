import { Company } from "../../slices/companiesSlice";
import React from "react";
import CompanyManagementActionMenu from "../CompanyManagementActionMenu/CompanyManagementActionMenu";
import { Column } from "@/components/Datatable/types";

interface CreateCompaniesColumnsProps {
  handleEdit: (uuid: string) => void;
}

export function createCompaniesColumns({
  handleEdit,
}: CreateCompaniesColumnsProps): Column<Company>[] {
  return [
    {
      header: "ID",
      accessorKey: "id",
      enableSorting: true,
    },
    {
      header: "Company Name",
      accessorKey: "companyName",
      enableSorting: true,
    },
    {
      header: "Salesforce ID",
      accessorKey: "salesforceId",
      enableSorting: true,
    },
    {
      header: "Intacct ID",
      accessorKey: "intacctId",
      enableSorting: true,
    },
    {
      header: "Stochastic Client",
      enableSorting: true,
      accessorKey: "marketingEnabled",
      cell: ({ row }) => (row.original.marketingEnabled ? "Yes" : "No"),
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const uuid = row.original.uuid || "";
        return (
          <CompanyManagementActionMenu companyUuid={uuid} onEdit={handleEdit} />
        );
      },
    },
  ];
}
