import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import ClearButton from "../../../../../../components/ClearButton/ClearButton";

interface LocationListFiltersProps {
  filters: { searchTerm?: string; isActive: number };
  onFilterChange: (searchTerm: string, isActive: number) => void;
}

const LocationListFilters: React.FC<LocationListFiltersProps> = ({
  filters,
  onFilterChange,
}) => {
  const [locationNameFilter, setLocationNameFilter] = useState(
    filters.searchTerm || "",
  );
  const [isActive, setIsActive] = useState(filters.isActive);
  const debouncedNameFilter = useDebouncedValue(locationNameFilter, 500);

  useEffect(() => {
    onFilterChange(debouncedNameFilter, isActive);
  }, [debouncedNameFilter, isActive]);

  const onFilterInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setLocationNameFilter(e.target.value);
  };

  const onClearFilter = () => {
    setLocationNameFilter("");
    onFilterChange("", isActive);
  };

  return (
    <div className="mb-4">
      <div className="flex items-center justify-start space-x-2">
        <div className="relative w-full max-w-md">
          <input
            className="px-3 py-2 pr-10 w-full flex h-9 rounded-md border border-input bg-transparent text-base shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
            onChange={onFilterInputChange}
            placeholder="Search by location name"
            type="text"
            value={locationNameFilter}
          />
          <ClearButton
            isVisible={!!locationNameFilter}
            onClick={onClearFilter}
          />
        </div>

        <select
          className="px-3 py-2 pr-10 flex h-9 rounded-md border border-input bg-transparent text-base shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
          onChange={(e) => setIsActive(parseInt(e.target.value, 10))}
          value={isActive}
        >
          <option value={1}>Active</option>
          <option value={0}>Inactive</option>
        </select>
      </div>
    </div>
  );
};

export default LocationListFilters;
