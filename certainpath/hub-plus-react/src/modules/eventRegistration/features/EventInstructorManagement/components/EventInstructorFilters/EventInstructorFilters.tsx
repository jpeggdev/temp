import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import SearchInput from "@/components/SearchInput/SearchInput";

interface EventInstructorFiltersProps {
  filters: {
    searchTerm: string;
  };
  onFilterChange: (filterKey: string, value: string) => void;
}

const EventInstructorFilters: React.FC<EventInstructorFiltersProps> = ({
  filters,
  onFilterChange,
}) => {
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
        onChange={(val) => setSearchInput(val)}
        placeholder="Search by name, email or phone..."
        value={searchInput}
      />
    </div>
  );
};

export default EventInstructorFilters;
