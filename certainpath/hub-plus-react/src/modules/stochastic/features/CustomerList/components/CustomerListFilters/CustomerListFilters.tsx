import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "../../../../../../hooks/useDebouncedValue";
import ClearButton from "../../../../../../components/ClearButton/ClearButton";

interface CustomerListFiltersProps {
  filters: { searchTerm?: string; isActive: number };
  onFilterChange: (searchTerm: string, isActive: number) => void;
}

const CustomerListFilters: React.FC<CustomerListFiltersProps> = ({
  filters,
  onFilterChange,
}) => {
  const [nameFilter, setNameFilter] = useState(filters.searchTerm || "");
  const [isActive, setIsActive] = useState(filters.isActive);
  const debouncedNameFilter = useDebouncedValue(nameFilter, 500);

  useEffect(() => {
    onFilterChange(debouncedNameFilter, isActive);
  }, [debouncedNameFilter, isActive]);

  const onFilterInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setNameFilter(e.target.value);
  };

  const onClearFilter = () => {
    setNameFilter("");
    onFilterChange("", isActive);
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

        <select
          className="border rounded-md px-2 py-2"
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

export default CustomerListFilters;
