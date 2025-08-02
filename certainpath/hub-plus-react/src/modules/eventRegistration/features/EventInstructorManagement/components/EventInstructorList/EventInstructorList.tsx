import React, { useCallback, useMemo, useState } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import DataTable from "@/components/Datatable/Datatable";
import { useEventInstructors } from "../../hooks/useEventInstructors";
import EventInstructorFilters from "../EventInstructorFilters/EventInstructorFilters";
import CreateEventInstructorDrawer from "../CreateEventInstructorDrawer/CreateEventInstructorDrawer";
import EditEventInstructorDrawer from "../EditEventInstructorDrawer/EditEventInstructorDrawer";
import DeleteEventInstructorModal from "../DeleteEventInstructorModal/DeleteEventInstructorModal";
import { eventInstructorColumns } from "@/modules/eventRegistration/features/EventInstructorManagement/components/EventInstructorColumns/EventInstructorColumns";

const EventInstructorList: React.FC = () => {
  const {
    instructors,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    sorting,
    handleFilterChange,
    handlePaginationChange,
    handleSortingChange,
    refetchInstructors,
  } = useEventInstructors();

  const [showCreateDrawer, setShowCreateDrawer] = useState(false);
  const [editId, setEditId] = useState<number | null>(null);
  const [showEditDrawer, setShowEditDrawer] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const handleCreateInstructor = useCallback(() => {
    setShowCreateDrawer(true);
  }, []);

  const handleEditInstructor = useCallback((id: number) => {
    setEditId(id);
    setShowEditDrawer(true);
  }, []);

  const handleDeleteInstructor = useCallback((id: number) => {
    setDeleteId(id);
    setShowDeleteModal(true);
  }, []);

  const handleDeleteSuccess = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
    refetchInstructors();
  }, [refetchInstructors]);

  const handleCloseDeleteModal = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
  }, []);

  const columns = useMemo(
    () =>
      eventInstructorColumns({
        onEditInstructor: handleEditInstructor,
        onDeleteInstructor: handleDeleteInstructor,
      }),
    [handleEditInstructor, handleDeleteInstructor],
  );

  return (
    <>
      <MainPageWrapper
        actions={
          <Button onClick={handleCreateInstructor}>Create Instructor</Button>
        }
        error={error}
        title="Event Instructors"
      >
        <EventInstructorFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />
        <DataTable
          columns={columns}
          data={instructors}
          error={error}
          loading={loading}
          noDataMessage="No instructors found"
          onPageChange={(newPageIndex, newPageSize) =>
            handlePaginationChange({
              pageIndex: newPageIndex,
              pageSize: newPageSize,
            })
          }
          onSortingChange={handleSortingChange}
          pageIndex={pagination.pageIndex}
          pageSize={pagination.pageSize}
          rowKeyExtractor={(item) => String(item.id)}
          sorting={sorting}
          totalCount={totalCount}
        />
      </MainPageWrapper>

      <CreateEventInstructorDrawer
        isOpen={showCreateDrawer}
        onClose={() => setShowCreateDrawer(false)}
        onSuccess={() => refetchInstructors()}
      />

      {editId !== null && (
        <EditEventInstructorDrawer
          instructorId={editId}
          isOpen={showEditDrawer}
          onClose={() => {
            setShowEditDrawer(false);
            setEditId(null);
          }}
          onSuccess={() => refetchInstructors()}
        />
      )}

      <DeleteEventInstructorModal
        instructorId={deleteId}
        isOpen={showDeleteModal}
        onClose={handleCloseDeleteModal}
        onSuccess={handleDeleteSuccess}
      />
    </>
  );
};

export default EventInstructorList;
