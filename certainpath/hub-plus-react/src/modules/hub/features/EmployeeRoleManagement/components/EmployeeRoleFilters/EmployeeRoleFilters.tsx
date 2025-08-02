import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import SearchInput from "@/components/SearchInput/SearchInput";

interface EmployeeRoleFiltersProps {
  filters: {
    name: string;
  };
  onFilterChange: (filterKey: string, value: string) => void;
}

const EmployeeRoleFilters: React.FC<EmployeeRoleFiltersProps> = ({
  filters,
  onFilterChange,
}) => {
  const [searchInput, setSearchInput] = useState(filters.name);
  const debouncedSearch = useDebouncedValue(searchInput, 500);

  useEffect(() => {
    if (debouncedSearch !== filters.name) {
      onFilterChange("name", debouncedSearch);
    }
  }, [debouncedSearch, filters.name, onFilterChange]);

  return (
    <div className="mb-4 space-y-4">
      <SearchInput
        onChange={(val) => setSearchInput(val)}
        placeholder="Search by employee role name..."
        value={searchInput}
      />
    </div>
  );
};

export default EmployeeRoleFilters;
