import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import SearchInput from "@/components/SearchInput/SearchInput";

interface EmailTemplateCategoryFiltersProps {
  filters: {
    name: string;
  };
  onFilterChange: (filterKey: string, value: string) => void;
}

const EmailTemplateCategoryFilters: React.FC<
  EmailTemplateCategoryFiltersProps
> = ({ filters, onFilterChange }) => {
  const [name, setName] = useState(filters.name);
  const debouncedName = useDebouncedValue(name, 500);

  useEffect(() => {
    if (debouncedName !== filters.name) {
      onFilterChange("name", debouncedName);
    }
  }, [debouncedName, filters.name, onFilterChange]);

  return (
    <div className="mb-4">
      <SearchInput
        onChange={setName}
        placeholder="Search by category name..."
        value={name}
      />
    </div>
  );
};

export default EmailTemplateCategoryFilters;
