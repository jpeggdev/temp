import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import { MultiSelect } from "@/components/MultiSelect/MultiSelect";
import SearchInput from "@/components/SearchInput/SearchInput";
import { EventFilterMetadataItem } from "../../slices/eventListSlice";

interface EventFiltersProps {
  filters: {
    searchTerm: string;
    tradeIds: string[];
    eventTypeIds: string[];
    employeeRoleIds: string[];
    categoryIds: string[];
    tagIds: string[];
  };
  onFilterChange: (filterKey: string, value: string | string[]) => void;
  eventTypes: EventFilterMetadataItem[];
  eventCategories: EventFilterMetadataItem[];
  employeeRoles: EventFilterMetadataItem[];
  trades: EventFilterMetadataItem[];
  eventTags: EventFilterMetadataItem[];
}

const EventFilters: React.FC<EventFiltersProps> = ({
  filters,
  onFilterChange,
  eventTypes,
  eventCategories,
  employeeRoles,
  trades,
  eventTags,
}) => {
  const [searchInput, setSearchInput] = useState(filters.searchTerm);
  const debouncedSearch = useDebouncedValue(searchInput, 500);

  useEffect(() => {
    if (debouncedSearch !== filters.searchTerm) {
      onFilterChange("searchTerm", debouncedSearch);
    }
  }, [debouncedSearch, filters.searchTerm, onFilterChange]);

  const typeOptions = eventTypes.map((et) => ({
    label: et.name,
    value: String(et.id),
  }));
  const categoryOptions = eventCategories.map((c) => ({
    label: c.name,
    value: String(c.id),
  }));
  const tradeOptions = trades.map((t) => ({
    label: t.name,
    value: String(t.id),
  }));
  const roleOptions = employeeRoles.map((r) => ({
    label: r.name,
    value: String(r.id),
  }));
  const tagOptions = eventTags.map((tg) => ({
    label: tg.name,
    value: String(tg.id),
  }));

  return (
    <div className="mb-4 space-y-4">
      <SearchInput
        className=""
        onChange={setSearchInput}
        placeholder="Search by event name..."
        value={searchInput}
      />

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <MultiSelect
          onChange={(vals) => onFilterChange("eventTypeIds", vals)}
          options={typeOptions}
          placeholder="Select event types..."
          value={filters.eventTypeIds}
        />
        <MultiSelect
          onChange={(vals) => onFilterChange("categoryIds", vals)}
          options={categoryOptions}
          placeholder="Select categories..."
          value={filters.categoryIds}
        />
        <MultiSelect
          onChange={(vals) => onFilterChange("tradeIds", vals)}
          options={tradeOptions}
          placeholder="Select trades..."
          value={filters.tradeIds}
        />
        <MultiSelect
          onChange={(vals) => onFilterChange("employeeRoleIds", vals)}
          options={roleOptions}
          placeholder="Select roles..."
          value={filters.employeeRoleIds}
        />
        <MultiSelect
          onChange={(vals) => onFilterChange("tagIds", vals)}
          options={tagOptions}
          placeholder="Select tags..."
          value={filters.tagIds}
        />
      </div>
    </div>
  );
};

export default EventFilters;
