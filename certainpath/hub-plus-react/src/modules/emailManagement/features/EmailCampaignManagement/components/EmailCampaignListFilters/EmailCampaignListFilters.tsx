import React, { useState, useEffect } from "react";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import SearchInput from "@/components/SearchInput/SearchInput";
import { EmailCampaignStatus } from "@/modules/emailManagement/features/EmailCampaignManagement/api/createEmailCampaign/types";

interface EmailCampaignListFiltersProps {
  filters: { searchTerm?: string; statusId?: number };
  emailCampaignStatuses: EmailCampaignStatus[];
  onFilterChange: (searchTerm: string, statusId?: number) => void;
}

const EmailCampaignListFilters: React.FC<EmailCampaignListFiltersProps> = ({
  filters,
  emailCampaignStatuses,
  onFilterChange,
}) => {
  const [searchTerm, setSearchTerm] = useState(filters.searchTerm || "");
  const [statusId, setStatusId] = useState<number | undefined>(
    filters.statusId,
  );
  const debouncedSearch = useDebouncedValue(searchTerm, 500);

  useEffect(() => {
    onFilterChange(debouncedSearch, statusId);
  }, [debouncedSearch, statusId, onFilterChange]);

  return (
    <div className="mb-4">
      <div className="flex items-center justify-start space-x-2">
        <div className="w-full max-w-md">
          <SearchInput
            className=""
            onChange={setSearchTerm}
            placeholder="Search by email campaign name"
            value={searchTerm}
          />
        </div>

        <select
          className="px-3 py-2 pr-10 flex h-9 rounded-md border border-input bg-transparent text-base shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
          onChange={(e) =>
            setStatusId(
              e.target.value ? parseInt(e.target.value, 10) : undefined,
            )
          }
          value={statusId ?? ""}
        >
          <option value="">All</option>
          {emailCampaignStatuses.map((status) => (
            <option key={status.id} value={status.id}>
              {status.displayName}
            </option>
          ))}
        </select>
      </div>
    </div>
  );
};

export default EmailCampaignListFilters;
