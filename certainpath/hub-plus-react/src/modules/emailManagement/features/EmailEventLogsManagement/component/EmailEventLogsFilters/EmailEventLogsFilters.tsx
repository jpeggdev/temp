import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import ClearButton from "../../../../../../components/ClearButton/ClearButton";
import { Input } from "@/components/ui/input";

interface EmailCampaignListFiltersProps {
  filters: { searchTerm?: string };
  onFilterChange: (searchTerm: string) => void;
}

const CustomerListFilters: React.FC<EmailCampaignListFiltersProps> = ({
  filters,
  onFilterChange,
}) => {
  const [searchTerm, setSearchTerm] = useState(filters.searchTerm || "");
  const debouncedNameFilter = useDebouncedValue(searchTerm, 500);

  useEffect(() => {
    onFilterChange(debouncedNameFilter);
  }, [debouncedNameFilter]);

  const onFilterInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setSearchTerm(e.target.value);
  };

  const onClearFilter = () => {
    setSearchTerm("");
    onFilterChange("");
  };

  return (
    <div className="mb-4">
      <div className="flex items-center justify-start space-x-2">
        <div className="relative w-full">
          <Input
            className="px-3 py-2 pr-10 w-full flex h-9 rounded-md border border-input bg-transparent text-base shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
            onChange={onFilterInputChange}
            placeholder="Filter..."
            type="text"
            value={searchTerm}
          />
          <ClearButton isVisible={!!searchTerm} onClick={onClearFilter} />
        </div>
      </div>
    </div>
  );
};

export default CustomerListFilters;
