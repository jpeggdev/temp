import React, { useState } from "react";
import { useDebouncedValue } from "../../../../../../hooks/useDebouncedValue";
import ClearButton from "../../../../../../components/ClearButton/ClearButton";

interface UserListFiltersProps {
  filters: {
    firstName?: string;
    lastName?: string;
    email?: string;
    salesforceId?: string;
  };
  onFilterChange: (filterKey: string, value: string) => void;
}

const UserListFilters: React.FC<UserListFiltersProps> = ({
  filters,
  onFilterChange,
}) => {
  const [firstNameFilter, setFirstNameFilter] = useState(
    filters.firstName || "",
  );
  const [lastNameFilter, setLastNameFilter] = useState(filters.lastName || "");
  const [emailFilter, setEmailFilter] = useState(filters.email || "");
  const [salesforceIdFilter, setSalesforceIdFilter] = useState(
    filters.salesforceId || "",
  );

  const debouncedFirstNameFilter = useDebouncedValue(firstNameFilter, 500);
  const debouncedLastNameFilter = useDebouncedValue(lastNameFilter, 500);
  const debouncedEmailFilter = useDebouncedValue(emailFilter, 500);
  const debouncedSalesforceIdFilter = useDebouncedValue(
    salesforceIdFilter,
    500,
  );

  React.useEffect(() => {
    onFilterChange("firstName", debouncedFirstNameFilter);
  }, [debouncedFirstNameFilter]);

  React.useEffect(() => {
    onFilterChange("lastName", debouncedLastNameFilter);
  }, [debouncedLastNameFilter]);

  React.useEffect(() => {
    onFilterChange("email", debouncedEmailFilter);
  }, [debouncedEmailFilter]);

  React.useEffect(() => {
    onFilterChange("salesforceId", debouncedSalesforceIdFilter);
  }, [debouncedSalesforceIdFilter]);

  const onFilterInputChange = (
    e: React.ChangeEvent<HTMLInputElement>,
    filterKey: string,
    setFilter: React.Dispatch<React.SetStateAction<string>>,
  ) => {
    setFilter(e.target.value);
  };

  const onClearFilter = (
    filterKey: string,
    setFilter: React.Dispatch<React.SetStateAction<string>>,
  ) => {
    setFilter("");
    onFilterChange(filterKey, "");
  };

  return (
    <div className="mb-4">
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary dark:focus:ring-primary dark:focus:border-primary"
            onChange={(e) =>
              onFilterInputChange(e, "firstName", setFirstNameFilter)
            }
            placeholder="Search by First Name"
            type="text"
            value={firstNameFilter}
          />
          <ClearButton
            isVisible={!!firstNameFilter}
            onClick={() => onClearFilter("firstName", setFirstNameFilter)}
          />
        </div>

        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary dark:focus:ring-primary dark:focus:border-primary"
            onChange={(e) =>
              onFilterInputChange(e, "lastName", setLastNameFilter)
            }
            placeholder="Search by Last Name"
            type="text"
            value={lastNameFilter}
          />
          <ClearButton
            isVisible={!!lastNameFilter}
            onClick={() => onClearFilter("lastName", setLastNameFilter)}
          />
        </div>

        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary dark:focus:ring-primary dark:focus:border-primary"
            onChange={(e) => onFilterInputChange(e, "email", setEmailFilter)}
            placeholder="Search by Email"
            type="text"
            value={emailFilter}
          />
          <ClearButton
            isVisible={!!emailFilter}
            onClick={() => onClearFilter("email", setEmailFilter)}
          />
        </div>

        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary dark:focus:ring-primary dark:focus:border-primary"
            onChange={(e) =>
              onFilterInputChange(e, "salesforceId", setSalesforceIdFilter)
            }
            placeholder="Search by Salesforce ID"
            type="text"
            value={salesforceIdFilter}
          />
          <ClearButton
            isVisible={!!salesforceIdFilter}
            onClick={() => onClearFilter("salesforceId", setSalesforceIdFilter)}
          />
        </div>
      </div>
    </div>
  );
};

export default UserListFilters;
