import React, { useState } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import ClearButton from "../../../../../../components/ClearButton/ClearButton";

interface CampaignProductListFiltersProps {
  filters: { searchTerm?: string };
  onFilterChange: (searchTerm: string) => void;
}

const CampaignProductListFilters: React.FC<CampaignProductListFiltersProps> = ({
  filters,
  onFilterChange,
}) => {
  const [nameFilter, setNameFilter] = useState(filters.searchTerm || "");
  const debouncedNameFilter = useDebouncedValue(nameFilter, 500);

  React.useEffect(() => {
    onFilterChange(debouncedNameFilter);
  }, [debouncedNameFilter]);

  const onFilterInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setNameFilter(e.target.value);
  };

  const onClearFilter = () => {
    setNameFilter("");
    onFilterChange("");
  };

  return (
    <div className="mb-4">
      <div className="flex items-center justify-start space-x-2">
        <div className="relative w-full max-w-md">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary dark:focus:ring-primary dark:focus:border-primary"
            onChange={onFilterInputChange}
            placeholder="Search by name"
            type="text"
            value={nameFilter}
          />
          <ClearButton isVisible={!!nameFilter} onClick={onClearFilter} />
        </div>
      </div>
    </div>
  );
};

export default CampaignProductListFilters;
