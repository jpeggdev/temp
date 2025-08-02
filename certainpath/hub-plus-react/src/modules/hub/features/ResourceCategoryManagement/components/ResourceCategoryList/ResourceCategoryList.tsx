import React, { useCallback, useMemo, useState } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import DataTable from "@/components/Datatable/Datatable";
import { useResourceCategories } from "../../hooks/useResourceCategories";
import { createResourceCategoryColumns } from "../ResourceCategoryColumns/ResourceCategoryColumns";
import ResourceCategoryFilters from "../ResourceCategoryFilters/ResourceCategoryFilters";
import CreateResourceCategoryDrawer from "../CreateResourceCategoryDrawer/CreateResourceCategoryDrawer";
import EditResourceCategoryDrawer from "../EditResourceCategoryDrawer/EditResourceCategoryDrawer";
import DeleteResourceCategoryModal from "../DeleteResourceCategoryModal/DeleteResourceCategoryModal";

const ResourceCategoryList: React.FC = () => {
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
  } = useResourceCategories();

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
  }, []);

  const handleCloseDeleteModal = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
  }, []);

  const columns = useMemo(
    () =>
      createResourceCategoryColumns({
        onEditCategory: handleEditCategory,
        onDeleteCategory: handleDeleteCategory,
      }),
    [handleEditCategory, handleDeleteCategory],
  );

  return (
    <>
      <MainPageWrapper
        actions={
          <Button onClick={handleCreateCategory}>Create Category</Button>
        }
        error={error}
        title="Resource Categories"
      >
        <ResourceCategoryFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />

        <DataTable
          columns={columns}
          data={categories}
          error={error}
          loading={loading}
          noDataMessage="No resource categories found"
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

      <CreateResourceCategoryDrawer
        isOpen={showCreateDrawer}
        onClose={() => setShowCreateDrawer(false)}
        onSuccess={() => {
          refetchCategories();
        }}
      />

      {editId !== null && (
        <EditResourceCategoryDrawer
          categoryId={editId}
          isOpen={showEditDrawer}
          onClose={() => {
            setShowEditDrawer(false);
            setEditId(null);
          }}
          onSuccess={() => {
            refetchCategories();
          }}
        />
      )}

      <DeleteResourceCategoryModal
        categoryId={deleteId}
        isOpen={showDeleteModal}
        onClose={handleCloseDeleteModal}
        onSuccess={handleDeleteSuccess}
      />
    </>
  );
};

export default ResourceCategoryList;
