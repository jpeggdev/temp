import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import ClearButton from "@/components/ClearButton/ClearButton";

interface DoNotMailFiltersProps {
  filters: {
    externalId?: string;
    address1?: string;
    address2?: string;
    city?: string;
    stateCode?: string;
    postalCode?: string;
    countryCode?: string;
    isBusiness?: string;
    isVacant?: string;
    isVerified?: string;
  };
  onFilterChange: (filterKey: string, value: string) => void;
}

const DoNotMailFilters: React.FC<DoNotMailFiltersProps> = ({
  filters,
  onFilterChange,
}) => {
  const [externalId] = useState(filters.externalId || "");
  const [address1, setAddress1] = useState(filters.address1 || "");
  const [address2, setAddress2] = useState(filters.address2 || "");
  const [city, setCity] = useState(filters.city || "");
  const [stateCode, setStateCode] = useState(filters.stateCode || "");
  const [postalCode, setPostalCode] = useState(filters.postalCode || "");
  const [countryCode, setCountryCode] = useState(filters.countryCode || "");
  const [isBusiness, setIsBusiness] = useState(filters.isBusiness || "");
  const [isVacant, setIsVacant] = useState(filters.isVacant || "");
  const [isVerified, setIsVerified] = useState(filters.isVerified || "");

  const debouncedExternalId = useDebouncedValue(externalId, 500);
  const debouncedAddress1 = useDebouncedValue(address1, 500);
  const debouncedAddress2 = useDebouncedValue(address2, 500);
  const debouncedCity = useDebouncedValue(city, 500);
  const debouncedStateCode = useDebouncedValue(stateCode, 500);
  const debouncedPostalCode = useDebouncedValue(postalCode, 500);
  const debouncedCountryCode = useDebouncedValue(countryCode, 500);
  const debouncedIsBusiness = useDebouncedValue(isBusiness, 500);
  const debouncedIsVacant = useDebouncedValue(isVacant, 500);
  const debouncedIsVerified = useDebouncedValue(isVerified, 500);

  // Sync changes with parent via onFilterChange when debounced values update
  useEffect(() => {
    onFilterChange("externalId", debouncedExternalId);
  }, [debouncedExternalId, onFilterChange]);

  useEffect(() => {
    onFilterChange("address1", debouncedAddress1);
  }, [debouncedAddress1, onFilterChange]);

  useEffect(() => {
    onFilterChange("address2", debouncedAddress2);
  }, [debouncedAddress2, onFilterChange]);

  useEffect(() => {
    onFilterChange("city", debouncedCity);
  }, [debouncedCity, onFilterChange]);

  useEffect(() => {
    onFilterChange("stateCode", debouncedStateCode);
  }, [debouncedStateCode, onFilterChange]);

  useEffect(() => {
    onFilterChange("postalCode", debouncedPostalCode);
  }, [debouncedPostalCode, onFilterChange]);

  useEffect(() => {
    onFilterChange("countryCode", debouncedCountryCode);
  }, [debouncedCountryCode, onFilterChange]);

  useEffect(() => {
    onFilterChange("isBusiness", debouncedIsBusiness);
  }, [debouncedIsBusiness, onFilterChange]);

  useEffect(() => {
    onFilterChange("isVacant", debouncedIsVacant);
  }, [debouncedIsVacant, onFilterChange]);

  useEffect(() => {
    onFilterChange("isVerified", debouncedIsVerified);
  }, [debouncedIsVerified, onFilterChange]);

  /**
   * Updates local state for text inputs.
   * If it's the stateCode or countryCode field, limit to 2 chars.
   */
  const handleTextChange = (
    e: React.ChangeEvent<HTMLInputElement>,
    setFilter: React.Dispatch<React.SetStateAction<string>>,
  ) => {
    const name = e.target.name;
    let value = e.target.value;

    // Only allow up to 2 characters for stateCode or countryCode
    if ((name === "stateCode" || name === "countryCode") && value.length > 2) {
      value = value.slice(0, 2);
    }

    setFilter(value);
  };

  const handleClear = (
    setFilter: React.Dispatch<React.SetStateAction<string>>,
    filterKey: string,
  ) => {
    setFilter("");
    onFilterChange(filterKey, "");
  };

  const handleBooleanSelect = (
    e: React.ChangeEvent<HTMLSelectElement>,
    setFilter: React.Dispatch<React.SetStateAction<string>>,
    filterKey: string,
  ) => {
    setFilter(e.target.value);
    onFilterChange(filterKey, e.target.value);
  };

  return (
    <div className="mb-4">
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
        {/* address1 */}
        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary"
            name="address1"
            onChange={(e) => handleTextChange(e, setAddress1)}
            placeholder="Address1"
            type="text"
            value={address1}
          />
          <ClearButton
            isVisible={!!address1}
            onClick={() => handleClear(setAddress1, "address1")}
          />
        </div>

        {/* address2 */}
        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary"
            name="address2"
            onChange={(e) => handleTextChange(e, setAddress2)}
            placeholder="Address2"
            type="text"
            value={address2}
          />
          <ClearButton
            isVisible={!!address2}
            onClick={() => handleClear(setAddress2, "address2")}
          />
        </div>

        {/* city */}
        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary"
            name="city"
            onChange={(e) => handleTextChange(e, setCity)}
            placeholder="City"
            type="text"
            value={city}
          />
          <ClearButton
            isVisible={!!city}
            onClick={() => handleClear(setCity, "city")}
          />
        </div>

        {/* stateCode */}
        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary"
            name="stateCode"
            onChange={(e) => handleTextChange(e, setStateCode)}
            placeholder="State Code"
            type="text"
            value={stateCode}
          />
          <ClearButton
            isVisible={!!stateCode}
            onClick={() => handleClear(setStateCode, "stateCode")}
          />
        </div>

        {/* postalCode */}
        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary"
            name="postalCode"
            onChange={(e) => handleTextChange(e, setPostalCode)}
            placeholder="Postal Code"
            type="text"
            value={postalCode}
          />
          <ClearButton
            isVisible={!!postalCode}
            onClick={() => handleClear(setPostalCode, "postalCode")}
          />
        </div>

        {/* countryCode */}
        <div className="relative w-full">
          <input
            className="px-3 py-2 pr-10 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary"
            name="countryCode"
            onChange={(e) => handleTextChange(e, setCountryCode)}
            placeholder="Country Code"
            type="text"
            value={countryCode}
          />
          <ClearButton
            isVisible={!!countryCode}
            onClick={() => handleClear(setCountryCode, "countryCode")}
          />
        </div>

        {/* isBusiness */}
        <div className="relative w-full">
          <select
            className="px-3 py-2 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary"
            onChange={(e) =>
              handleBooleanSelect(e, setIsBusiness, "isBusiness")
            }
            value={isBusiness}
          >
            <option value="">Is Business?</option>
            <option value="true">Yes</option>
            <option value="false">No</option>
          </select>
        </div>

        {/* isVacant */}
        <div className="relative w-full">
          <select
            className="px-3 py-2 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary"
            onChange={(e) => handleBooleanSelect(e, setIsVacant, "isVacant")}
            value={isVacant}
          >
            <option value="">Is Vacant?</option>
            <option value="true">Yes</option>
            <option value="false">No</option>
          </select>
        </div>

        {/* isVerified */}
        <div className="relative w-full">
          <select
            className="px-3 py-2 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary"
            onChange={(e) =>
              handleBooleanSelect(e, setIsVerified, "isVerified")
            }
            value={isVerified}
          >
            <option value="">Is Verified?</option>
            <option value="true">Yes</option>
            <option value="false">No</option>
          </select>
        </div>
      </div>
    </div>
  );
};

export default DoNotMailFilters;
