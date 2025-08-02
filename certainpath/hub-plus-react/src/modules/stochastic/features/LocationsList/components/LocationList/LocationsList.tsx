import React, { useMemo } from "react";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import DataTable from "@/components/Datatable/Datatable";
import { useLocations } from "@/modules/stochastic/features/LocationsList/hooks/useLocations";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Button } from "@/components/Button/Button";
import CreateLocationDrawer from "@/modules/stochastic/features/LocationsList/components/CreateLocationDrawer/CreateLocationDrawer";
import { Location } from "@/modules/stochastic/features/LocationsList/api/createLocation/types";
import DeleteLocationModal from "@/modules/stochastic/features/LocationsList/components/DeleteLocationModal/DeleteLocationModal";
import { LocationListColumns } from "@/modules/stochastic/features/LocationsList/components/LocationListColumns/LocationListColumns";
import EditLocationDrawer from "@/modules/stochastic/features/LocationsList/components/EditLocationDrawer/EditLocationDrawer";
import LocationListFilters from "@/modules/stochastic/features/LocationsList/components/LocationListFilters/LocationListFilters";

const LocationsList: React.FC = () => {
  const {
    editId,
    deleteId,
    locations,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    handleFilterChange,
    showCreateDrawer,
    showEditDrawer,
    showDeleteModal,
    openCreateDrawer,
    openEditDrawer,
    openDeleteModal,
    closeEditDrawer,
    closeDeleteModal,
    handleDeleteSuccessError,
    refetchLocations,
    setShowCreateDrawer,
    handlePaginationChange,
    handleSortingChange,
  } = useLocations();

  const columns = useMemo(
    () =>
      LocationListColumns({
        onEditLocation: openEditDrawer,
        onDeleteLocation: openDeleteModal,
      }),
    [openEditDrawer, openDeleteModal],
  );

  return (
    <>
      <MainPageWrapper
        actions={
          <ShowIfHasAccess requiredPermissions={["CAN_CREATE_CAMPAIGNS"]}>
            <Button onClick={openCreateDrawer}>Create Location</Button>
          </ShowIfHasAccess>
        }
        title="Locations"
      >
        <LocationListFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />

        <div className="relative">
          <DataTable<Location>
            columns={columns}
            data={locations}
            error={error}
            loading={loading}
            noDataMessage="No locations found"
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
            totalCount={totalCount}
          />
        </div>
      </MainPageWrapper>

      <CreateLocationDrawer
        isOpen={showCreateDrawer}
        onClose={() => setShowCreateDrawer(false)}
        onSuccess={refetchLocations}
      />

      {editId !== null && (
        <EditLocationDrawer
          isOpen={showEditDrawer}
          locationId={editId}
          onClose={closeEditDrawer}
          onSuccess={refetchLocations}
        />
      )}

      <DeleteLocationModal
        isOpen={showDeleteModal}
        locationId={deleteId}
        onClose={closeDeleteModal}
        onError={handleDeleteSuccessError}
        onSuccess={handleDeleteSuccessError}
      />
    </>
  );
};

export default LocationsList;
