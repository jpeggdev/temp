import React from "react";
import { QuickBooksReport } from "../../../../../../api/fetchQuickBooksReports/types";
import QuickBooksReportsActionMenu from "../QuickBooksReportsActionMenu/QuickBooksReportsActionMenu";
import { Column } from "@/components/Datatable/types";

interface QuickBooksReportsColumnsProps {
  handleDownloadReport: (reportId: string) => void;
}

export function createQuickBooksReportsColumns({
  handleDownloadReport,
}: QuickBooksReportsColumnsProps): Column<QuickBooksReport>[] {
  return [
    {
      header: "Name",
      accessorKey: "name",
    },
    {
      header: "Date",
      accessorKey: "date",
      cell: ({ row }) => {
        const date = new Date(row.original.date);
        return date.toLocaleDateString(undefined, { timeZone: "UTC" });
      },
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => (
        <QuickBooksReportsActionMenu
          onDownloadReport={handleDownloadReport}
          report={row.original}
        />
      ),
    },
  ];
}
