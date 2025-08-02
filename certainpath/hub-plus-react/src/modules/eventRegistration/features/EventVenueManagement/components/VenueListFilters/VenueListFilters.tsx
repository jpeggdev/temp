import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import SearchInput from "@/components/SearchInput/SearchInput";

interface VenueListProps {
  filters: { searchTerm?: string; isActive: number };
  onFilterChange: (searchTerm: string, isActive: number) => void;
}

const VenueListFilters: React.FC<VenueListProps> = ({
  filters,
  onFilterChange,
}) => {
  const [setSearchTermFilter, setSetSearchTermFilter] = useState(
    filters.searchTerm || "",
  );
  const [isActive, setIsActive] = useState(filters.isActive);
  const debouncedNameFilter = useDebouncedValue(setSearchTermFilter, 500);

  useEffect(() => {
    onFilterChange(debouncedNameFilter, isActive);
  }, [debouncedNameFilter, isActive, onFilterChange]);

  return (
    <div className="mb-4">
      <div className="flex items-center justify-start space-x-2">
        <div className="w-full max-w-md">
          <SearchInput
            className="w-full"
            onChange={setSetSearchTermFilter}
            placeholder="Search by Name"
            value={setSearchTermFilter}
          />
        </div>

        <select
          className="px-3 py-2 pr-10 flex h-9 rounded-md border border-input bg-transparent text-base shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
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

export default VenueListFilters;
