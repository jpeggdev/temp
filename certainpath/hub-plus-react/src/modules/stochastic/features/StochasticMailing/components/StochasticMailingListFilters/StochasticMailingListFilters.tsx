import * as React from "react";
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Button } from "@/components/Button/Button";
import { Pencil } from "lucide-react";

interface StochasticMailingListFiltersProps {
  filters: {
    week?: number;
    year?: number;
  };
  isDownloading: boolean;
  isDownloadingSummary: boolean;
  isBilling: boolean;
  showBillButton: boolean;
  onDownloadCLIFileButtonClick?: () => void;
  onDownloadSummaryButtonClick?: () => void;
  onFilterChange: (key: string, value: number) => void;
  onUpdateBatchStatusButtonClick?: () => void;
  onBillBatchesButtonClick?: () => void;
}

const StochasticMailingListFilters: React.FC<
  StochasticMailingListFiltersProps
> = ({
  filters,
  isDownloading,
  isDownloadingSummary,
  isBilling,
  showBillButton,
  onFilterChange,
  onDownloadCLIFileButtonClick,
  onDownloadSummaryButtonClick,
  onUpdateBatchStatusButtonClick,
  onBillBatchesButtonClick,
}) => {
  const currentDate = new Date();
  const systemYear = currentDate.getFullYear();
  const currentWeek = filters.week ?? 1;
  const currentYear = filters.year ?? systemYear;
  const yearOptions = [systemYear - 1, systemYear, systemYear + 1];
  const weekOptions = Array.from({ length: 52 }, (_, i) => i + 1);

  return (
    <div className="mb-4 flex flex-wrap gap-4 items-end justify-between">
      <div className="flex gap-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Year
          </label>
          <Select
            onValueChange={(value) => onFilterChange("year", Number(value))}
            value={String(currentYear)}
          >
            <SelectTrigger className="w-[180px]">
              <SelectValue placeholder="Select a year" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectLabel>Years</SelectLabel>
                {yearOptions.map((year) => (
                  <SelectItem key={year} value={String(year)}>
                    {year}
                  </SelectItem>
                ))}
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Week
          </label>
          <Select
            onValueChange={(value) => onFilterChange("week", Number(value))}
            value={String(currentWeek)}
          >
            <SelectTrigger className="w-[180px]">
              <SelectValue placeholder="Select a week" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectLabel>Weeks</SelectLabel>
                {weekOptions.map((week) => (
                  <SelectItem key={week} value={String(week)}>
                    {week}
                  </SelectItem>
                ))}
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div className="flex gap-3">
        {showBillButton && (
          <Button onClick={onBillBatchesButtonClick} size="sm">
            {isBilling ? "Billing..." : "Bill Selected Batches"}
          </Button>
        )}
        <Button
          disabled={isDownloadingSummary}
          onClick={onDownloadSummaryButtonClick}
          size="sm"
        >
          {isDownloadingSummary ? "Downloading..." : "Download Summary"}
        </Button>

        <Button
          disabled={isDownloading}
          onClick={onDownloadCLIFileButtonClick}
          size="sm"
        >
          {isDownloading ? "Downloading..." : "Download CLI file"}
        </Button>
        <Button
          aria-label="Edit"
          onClick={onUpdateBatchStatusButtonClick}
          size="sm"
          variant="default"
        >
          <Pencil className="w-4 h-4" />
        </Button>
      </div>
    </div>
  );
};

export default StochasticMailingListFilters;
