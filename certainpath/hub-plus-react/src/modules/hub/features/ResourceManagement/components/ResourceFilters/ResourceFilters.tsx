import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import { MultiSelect } from "@/components/MultiSelect/MultiSelect";
import SearchInput from "@/components/SearchInput/SearchInput";

interface FilterMetaItem {
  id: number;
  name: string;
}

interface ResourceFiltersProps {
  filters: {
    searchTerm: string;
    tradeIds: string[];
    resourceTypeIds: string[];
    employeeRoleIds: string[];
  };
  onFilterChange: (filterKey: string, value: string | string[]) => void;
  resourceTypes: FilterMetaItem[];
  trades: FilterMetaItem[];
  employeeRoles: FilterMetaItem[];
}

const ResourceFilters: React.FC<ResourceFiltersProps> = ({
  filters,
  onFilterChange,
  resourceTypes,
  trades,
  employeeRoles,
}) => {
  const [searchInput, setSearchInput] = useState(filters.searchTerm);
  const debouncedSearch = useDebouncedValue(searchInput, 500);

  useEffect(() => {
    if (debouncedSearch !== filters.searchTerm) {
      onFilterChange("searchTerm", debouncedSearch);
    }
  }, [debouncedSearch, filters.searchTerm, onFilterChange]);

  const resourceTypeOptions = resourceTypes.map((rt) => ({
    label: rt.name,
    value: String(rt.id),
  }));
  const tradeOptions = trades.map((t) => ({
    label: t.name,
    value: String(t.id),
  }));
  const roleOptions = employeeRoles.map((er) => ({
    label: er.name,
    value: String(er.id),
  }));

  return (
    <div className="mb-4 space-y-4">
      <SearchInput
        onChange={(val) => setSearchInput(val)}
        placeholder="Search by title..."
        value={searchInput}
      />

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <MultiSelect
          onChange={(newVals) => onFilterChange("resourceTypeIds", newVals)}
          options={resourceTypeOptions}
          placeholder="Select resource types..."
          value={filters.resourceTypeIds}
        />

        <MultiSelect
          onChange={(newVals) => onFilterChange("tradeIds", newVals)}
          options={tradeOptions}
          placeholder="Select trades..."
          value={filters.tradeIds}
        />

        <MultiSelect
          onChange={(newVals) => onFilterChange("employeeRoleIds", newVals)}
          options={roleOptions}
          placeholder="Select roles..."
          value={filters.employeeRoleIds}
        />
      </div>
    </div>
  );
};

export default ResourceFilters;
