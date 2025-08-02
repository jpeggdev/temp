import React from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { EllipsisVerticalIcon } from "@heroicons/react/24/outline";
import { CompanyDataImportJob } from "../../graphql/subscriptions/onCompanyDataImportJob/types";

interface ImportStatusActionMenuProps {
  job: CompanyDataImportJob;
  onDownload: (uuid: string) => void;
  onErrorClick: (error: string) => void;
}

const ImportStatusActionMenu: React.FC<ImportStatusActionMenuProps> = ({
  job,
  onDownload,
  onErrorClick,
}) => {
  const hasFile = !!job.file_path;
  const hasError = !!job.error_message;

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          className="h-8 w-8 p-0 hover:bg-gray-200 transition-colors"
          variant="ghost"
        >
          <span className="sr-only">Open menu</span>
          <EllipsisVerticalIcon className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>

      <DropdownMenuContent align="end" className="w-[140px] p-2 bg-white">
        {hasFile && (
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
            onClick={() => onDownload(job.uuid)}
          >
            Download File
          </DropdownMenuItem>
        )}

        {hasError && (
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
            onClick={() => onErrorClick(job.error_message as string)}
          >
            View Error
          </DropdownMenuItem>
        )}
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default ImportStatusActionMenu;
