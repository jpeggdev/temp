import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import SearchInput from "@/components/SearchInput/SearchInput";

interface EventSessionFiltersProps {
  filters: {
    searchTerm: string;
  };
  onFilterChange: (filterKey: string, value: string | string[]) => void;
}

function EventSessionFilters({
  filters,
  onFilterChange,
}: EventSessionFiltersProps) {
  const [searchInput, setSearchInput] = useState(filters.searchTerm);
  const debouncedSearch = useDebouncedValue(searchInput, 500);

  useEffect(() => {
    if (debouncedSearch !== filters.searchTerm) {
      onFilterChange("searchTerm", debouncedSearch);
    }
  }, [debouncedSearch, filters.searchTerm, onFilterChange]);

  return (
    <div className="mb-4 space-y-4">
      <SearchInput
        onChange={setSearchInput}
        placeholder="Search sessions..."
        value={searchInput}
      />
    </div>
  );
}

export default EventSessionFilters;
