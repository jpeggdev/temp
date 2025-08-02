import React, { useMemo } from "react";
import { CampaignStatus } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignStatuses/types";

interface CampaignListFiltersProps {
  campaignStatuses: CampaignStatus[];
  campaignStatusId?: number;
  onFilterChange: (campaignStatusId?: number) => void;
}

const CampaignListFilters: React.FC<CampaignListFiltersProps> = ({
  campaignStatuses,
  campaignStatusId,
  onFilterChange,
}) => {
  const sortedStatuses = useMemo(() => {
    return [...campaignStatuses].sort((a, b) => a.name.localeCompare(b.name));
  }, [campaignStatuses]);

  const handleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const val = e.target.value;
    onFilterChange(val ? Number(val) : undefined);
  };

  return (
    <div className="mb-4">
      <label
        className="block text-sm font-medium text-gray-900"
        htmlFor="campaignStatusFilter"
      >
        Campaign Status
      </label>
      <select
        className="mt-1 block w-52 rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        id="campaignStatusFilter"
        name="campaignStatusFilter"
        onChange={handleChange}
        value={campaignStatusId ?? ""}
      >
        <option value="">All</option>
        {sortedStatuses.map((status) => {
          const displayName =
            status.name.charAt(0).toUpperCase() + status.name.slice(1);

          return (
            <option key={status.id} value={status.id}>
              {displayName}
            </option>
          );
        })}
      </select>
    </div>
  );
};

export default CampaignListFilters;
