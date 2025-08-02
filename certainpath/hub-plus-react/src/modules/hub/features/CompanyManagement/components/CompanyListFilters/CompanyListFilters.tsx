// src/features/companies/components/CompanyListFilters.tsx

import React, { useState } from "react";
import { useDebouncedValue } from "../../../../../../hooks/useDebouncedValue";
import ClearButton from "../../../../../../components/ClearButton/ClearButton";

interface CompanyListFiltersProps {
  filters: {
    searchTerm?: string;
  };
  onFilterChange: (filterKey: string, value: string) => void;
}

const CompanyListFilters: React.FC<CompanyListFiltersProps> = ({
  filters,
  onFilterChange,
}) => {
  const [searchTerm, setSearchTerm] = useState(filters.searchTerm || "");

  const debouncedSearchTerm = useDebouncedValue(searchTerm, 500);

  React.useEffect(() => {
    onFilterChange("searchTerm", debouncedSearchTerm);
  }, [debouncedSearchTerm]);

  const onFilterInputChange = (
    e: React.ChangeEvent<HTMLInputElement>,
    setFilter: React.Dispatch<React.SetStateAction<string>>,
  ) => {
    setFilter(e.target.value);
  };

  const onClearFilter = (
    setFilter: React.Dispatch<React.SetStateAction<string>>,
  ) => {
    setFilter("");
    onFilterChange("searchTerm", "");
  };

  return (
    <div className="mb-4">
      <div className="grid grid-cols-1 gap-4">
        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary dark:focus:ring-primary dark:focus:border-primary"
            onChange={(e) => onFilterInputChange(e, setSearchTerm)}
            placeholder="Search Companies"
            type="text"
            value={searchTerm}
          />
          <ClearButton
            isVisible={!!searchTerm}
            onClick={() => onClearFilter(setSearchTerm)}
          />
        </div>
      </div>
    </div>
  );
};

export default CompanyListFilters;
