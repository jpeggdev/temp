import React, { useCallback, useMemo, useState } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import DataTable from "@/components/Datatable/Datatable";
import { useEventCategories } from "../../hooks/useEventCategories";
import EventCategoryFilters from "../EventCategoryFilters/EventCategoryFilters";
import CreateEventCategoryDrawer from "../CreateEventCategoryDrawer/CreateEventCategoryDrawer";
import EditEventCategoryDrawer from "../EditEventCategoryDrawer/EditEventCategoryDrawer";
import DeleteEventCategoryModal from "../DeleteEventCategoryModal/DeleteEventCategoryModal";
import { eventCategoryColumns } from "@/modules/eventRegistration/features/EventCategoryManagement/components/EventCategoryColumns/EventCategoryColumns";

const EventCategoryList: React.FC = () => {
  const {
    categories,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    sorting,
    handleFilterChange,
    handlePaginationChange,
    handleSortingChange,
    refetchCategories,
  } = useEventCategories();

  const [showCreateDrawer, setShowCreateDrawer] = useState(false);
  const [editId, setEditId] = useState<number | null>(null);
  const [showEditDrawer, setShowEditDrawer] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const handleCreateCategory = useCallback(() => {
    setShowCreateDrawer(true);
  }, []);

  const handleEditCategory = useCallback((id: number) => {
    setEditId(id);
    setShowEditDrawer(true);
  }, []);

  const handleDeleteCategory = useCallback((id: number) => {
    setDeleteId(id);
    setShowDeleteModal(true);
  }, []);

  const handleDeleteSuccess = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
    refetchCategories();
  }, [refetchCategories]);

  const handleCloseDeleteModal = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
  }, []);

  const columns = useMemo(
    () =>
      eventCategoryColumns({
        onEditCategory: handleEditCategory,
        onDeleteCategory: handleDeleteCategory,
      }),
    [handleEditCategory, handleDeleteCategory],
  );

  return (
    <>
      <MainPageWrapper
        actions={
          <Button onClick={handleCreateCategory}>Create Event Category</Button>
        }
        error={error}
        title="Event Categories"
      >
        <EventCategoryFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />
        <DataTable
          columns={columns}
          data={categories}
          error={error}
          loading={loading}
          noDataMessage="No event categories found"
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

      <CreateEventCategoryDrawer
        isOpen={showCreateDrawer}
        onClose={() => setShowCreateDrawer(false)}
        onSuccess={() => refetchCategories()}
      />

      {editId !== null && (
        <EditEventCategoryDrawer
          categoryId={editId}
          isOpen={showEditDrawer}
          onClose={() => {
            setShowEditDrawer(false);
            setEditId(null);
          }}
          onSuccess={() => refetchCategories()}
        />
      )}

      <DeleteEventCategoryModal
        categoryId={deleteId}
        isOpen={showDeleteModal}
        onClose={handleCloseDeleteModal}
        onSuccess={handleDeleteSuccess}
      />
    </>
  );
};

export default EventCategoryList;
