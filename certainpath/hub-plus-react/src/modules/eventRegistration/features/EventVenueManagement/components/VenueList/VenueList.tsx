import React, { useMemo } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Button } from "@/components/Button/Button";
import { useNavigate } from "react-router-dom";
import DataTable from "@/components/Datatable/Datatable";
import { venueColumns } from "@/modules/eventRegistration/features/EventVenueManagement/components/VenueColumns/VenueColumns";
import { Venue } from "@/modules/eventRegistration/features/EventVenueManagement/api/createVenue/types";
import { useVenueList } from "@/modules/eventRegistration/features/EventVenueManagement/hooks/useVenueList";
import VenueListFilters from "@/modules/eventRegistration/features/EventVenueManagement/components/VenueListFilters/VenueListFilters";
import DeleteVenueModal from "@/modules/eventRegistration/features/EventVenueManagement/components/DeleteVenueModal/DeleteVenueModal";
import { useDeleteVenue } from "@/modules/eventRegistration/features/EventVenueManagement/hooks/useDeleteVenue";

const VenueList: React.FC = () => {
  const navigate = useNavigate();

  const {
    venues,
    loading,
    error,
    totalCount,
    filters,
    pagination,
    refetchVenues,
    handleFilterChange,
    handleEditVenue,
    handleSortingChange,
    handlePaginationChange,
  } = useVenueList();

  const {
    isDeleting,
    showDeleteModal,
    handleShowDeleteModal,
    handleCloseDeleteModal,
    handleDelete,
  } = useDeleteVenue({ refetchVenues });

  const columns = useMemo(
    () =>
      venueColumns({
        handleEditVenue,
        handleShowDeleteModal,
      }),
    [],
  );

  return (
    <>
      <MainPageWrapper
        actions={
          <ShowIfHasAccess requiredRoles={["ROLE_SUPER_ADMIN"]}>
            <Button
              disabled={false}
              onClick={() => {
                navigate("/event-registration/admin/venue/new");
              }}
            >
              New Venue
            </Button>
          </ShowIfHasAccess>
        }
        error={error}
        title="Venues"
      >
        <VenueListFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />

        <div className="relative">
          <DataTable<Venue>
            columns={columns}
            data={venues}
            error={error}
            loading={loading}
            noDataMessage="No venues found"
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

      <DeleteVenueModal
        handleDelete={handleDelete}
        isDeleting={isDeleting}
        isOpen={showDeleteModal}
        onClose={handleCloseDeleteModal}
      />
    </>
  );
};

export default VenueList;
