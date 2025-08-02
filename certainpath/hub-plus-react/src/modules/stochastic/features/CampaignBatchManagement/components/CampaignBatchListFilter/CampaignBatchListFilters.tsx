import React, { useMemo } from "react";
import { BatchStatus } from "@/api/fetchBatchStatuses/types";

interface BatchListFiltersProps {
  batchStatuses: BatchStatus[];
  batchStatusId?: number;
  onFilterChange: (batchStatusId?: number) => void;
}

const CampaignBatchListFilters: React.FC<BatchListFiltersProps> = ({
  batchStatuses,
  batchStatusId,
  onFilterChange,
}) => {
  const sortedStatuses = useMemo(() => {
    return [...batchStatuses].sort((a, b) => a.name.localeCompare(b.name));
  }, [batchStatuses]);

  const handleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const val = e.target.value;
    onFilterChange(val ? Number(val) : undefined);
  };

  return (
    <div className="mb-4">
      <label
        className="block text-sm font-medium text-gray-900"
        htmlFor="batchStatusFilter"
      >
        Batch Status
      </label>
      <select
        className="mt-1 block w-52 rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        id="batchStatusFilter"
        name="batchStatusFilter"
        onChange={handleChange}
        value={batchStatusId ?? ""}
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

export default CampaignBatchListFilters;
