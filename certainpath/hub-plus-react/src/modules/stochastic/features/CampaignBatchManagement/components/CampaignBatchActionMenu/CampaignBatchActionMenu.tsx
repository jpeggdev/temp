import React from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { EllipsisVerticalIcon } from "@heroicons/react/24/outline";
import { Batch } from "../../../../../../api/getCampaignBatches/types";

interface CampaignBatchActionMenuProps {
  batch: Batch;
  onViewProspects: (id: number) => void;
  onDownloadCsv: (id: number) => void;
  onOpenArchiveBatchModal: (id: number) => void;
}

const CampaignBatchActionMenu: React.FC<CampaignBatchActionMenuProps> = ({
  batch,
  onViewProspects,
  onDownloadCsv,
  onOpenArchiveBatchModal,
}) => {
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
        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
          onClick={() => onViewProspects(batch.id)}
        >
          View Prospects
        </DropdownMenuItem>

        <DropdownMenuItem
          className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
          onClick={() => onDownloadCsv(batch.id)}
        >
          Download CSV
        </DropdownMenuItem>

        {/* Only show Archive if batchStatus is "new" */}
        {batch.batchStatus?.name === "new" && (
          <DropdownMenuItem
            className="flex items-center px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
            onClick={() => onOpenArchiveBatchModal(batch.id)}
          >
            Archive Batch
          </DropdownMenuItem>
        )}
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default CampaignBatchActionMenu;
