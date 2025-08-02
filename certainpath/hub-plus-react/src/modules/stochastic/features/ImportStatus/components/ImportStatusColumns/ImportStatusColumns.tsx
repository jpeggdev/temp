import React from "react";
import { CompanyDataImportJob } from "../../graphql/subscriptions/onCompanyDataImportJob/types";
import { Progress } from "@/components/ui/progress";
import ImportStatusActionMenu from "../ImportStatusActionMenu/ImportStatusActionMenu";
import { Column } from "@/components/Datatable/types";

function getFileType(imp: CompanyDataImportJob) {
  if (imp.is_jobs_or_invoice_file) return "Invoice File";
  if (imp.is_active_club_member_file) return "Active Members File";
  if (imp.is_member_file) return "Member File";
  if (imp.is_prospects_file) return "Prospect File";
  return "Unknown Type";
}

interface CreateImportStatusColumnsProps {
  onErrorClick: (error: string) => void;
  onDownloadClick: (uuid: string) => void;
}

export function createImportStatusColumns({
  onErrorClick,
  onDownloadClick,
}: CreateImportStatusColumnsProps): Column<CompanyDataImportJob>[] {
  return [
    {
      accessorKey: "status",
      header: "Status",
    },
    {
      id: "progress",
      header: "Progress",
      cell: ({ row }) => {
        const imp = row.original;
        const percentage = imp.progress_percent ?? 0;
        return (
          <div className="flex flex-col items-center space-y-1">
            <Progress className="w-full" value={percentage} />
            <span className="text-xs text-gray-600">
              {percentage.toFixed(0)}%
            </span>
          </div>
        );
      },
    },
    {
      id: "type",
      header: "Type",
      cell: ({ row }) => {
        const imp = row.original;
        return getFileType(imp);
      },
    },
    {
      accessorKey: "software",
      header: "Software",
    },
    {
      accessorKey: "trade",
      header: "Trade",
    },
    {
      accessorKey: "tag",
      header: "Tags",
      cell: ({ row }) => {
        const imp = row.original;
        return imp.tag ? imp.tag.split(",").join(", ") : "";
      },
    },
    {
      id: "createdAt",
      header: "Created At",
      cell: ({ row }) => {
        const imp = row.original;
        return new Date(imp.created_at).toLocaleString();
      },
    },
    {
      id: "actions",
      header: "Actions",
      cell: ({ row }) => {
        const imp = row.original;
        return (
          <ImportStatusActionMenu
            job={imp}
            onDownload={onDownloadClick}
            onErrorClick={onErrorClick}
          />
        );
      },
    },
  ];
}
