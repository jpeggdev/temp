import React, { useCallback, useMemo } from "react";
import { useDispatch } from "react-redux";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import ProspectListFilters from "../ProspectListFilters/ProspectListFilters";
import { useStochasticProspects } from "../../hooks/useStochasticProspects";
import { createStochasticProspectsColumns } from "../ProspectsColumns/ProspectsColumns";
import { StochasticProspect } from "@/api/fetchStochasticProspects/types";
import DataTable from "@/components/Datatable/Datatable";
import { updateProspectDoNotMail } from "@/api/updateProspectDoNotMail/updateProspectDoNotMailApi";
import { toast } from "@/components/ui/use-toast";
import { updateProspectDoNotMail as updateProspectDoNotMailAction } from "../../slices/stochasticProspectsSlice";

const ProspectList: React.FC = () => {
  const dispatch = useDispatch();
  const {
    prospects,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    handlePaginationChange,
    handleFilterChange,
    handleSortingChange,
    filters,
  } = useStochasticProspects();

  const handleToggleDoNotMail = useCallback(
    async (prospectId: number, newValue: boolean) => {
      // Optimistically update the UI immediately
      dispatch(
        updateProspectDoNotMailAction({ prospectId, doNotMail: newValue }),
      );

      try {
        await updateProspectDoNotMail(prospectId, { doNotMail: newValue });
        toast({
          title: "Success",
          description: "Prospect mail preference updated successfully.",
        });
      } catch {
        // Revert the optimistic update on error
        dispatch(
          updateProspectDoNotMailAction({ prospectId, doNotMail: !newValue }),
        );
        toast({
          title: "Error",
          description: "Failed to update prospect mail preference.",
          variant: "destructive",
        });
      }
    },
    [dispatch],
  );

  const columns = useMemo(
    () =>
      createStochasticProspectsColumns({
        onToggleDoNotMail: handleToggleDoNotMail,
      }),
    [handleToggleDoNotMail],
  );

  const onFilterChange = useCallback(
    (searchTerm: string) => {
      handleFilterChange(searchTerm);
      handlePaginationChange({
        pageIndex: 0,
        pageSize: pagination.pageSize,
      });
    },
    [handleFilterChange, handlePaginationChange, pagination.pageSize],
  );

  return (
    <MainPageWrapper error={error} title="Prospects">
      <ProspectListFilters filters={filters} onFilterChange={onFilterChange} />

      <div className="relative">
        <DataTable<StochasticProspect>
          columns={columns}
          data={prospects}
          error={error}
          loading={loading}
          noDataMessage="No prospects found"
          onPageChange={(newPageIndex, newPageSize) =>
            handlePaginationChange({
              pageIndex: newPageIndex,
              pageSize: newPageSize,
            })
          }
          onSortingChange={handleSortingChange}
          pageIndex={pagination.pageIndex}
          pageSize={pagination.pageSize}
          rowKeyExtractor={(item) => item.id}
          sorting={sorting}
          totalCount={totalCount}
        />
      </div>
    </MainPageWrapper>
  );
};

export default ProspectList;
