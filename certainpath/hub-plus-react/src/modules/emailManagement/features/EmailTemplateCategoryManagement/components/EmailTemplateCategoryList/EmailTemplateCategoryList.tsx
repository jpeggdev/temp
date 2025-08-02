import React, { useCallback, useMemo, useState } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import DataTable from "@/components/Datatable/Datatable";
import { useEmailTemplateCategories } from "../../hooks/useEmailTemplateCategories";
import EmailTemplateCategoryFilters from "../EmailTemplateCategoryFilters/EmailTemplateCategoryFilters";
import CreateEmailTemplateCategoryDrawer from "../CreateEmailTemplateCategoryDrawer/CreateEmailTemplateCategoryDrawer";
import EditEmailTemplateCategoryDrawer from "../EditEmailTemplateCategoryDrawer/EditEmailTemplateCategoryDrawer";
import DeleteEmailTemplateCategoryModal from "../DeleteEmailTemplateCategoryModal/DeleteEmailTemplateCategoryModal";
import { createEmailTemplateCategoryColumns } from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/components/CreateEmailTemplateCategoryColumns/CreateEmailTemplateCategoryColumns";

const EmailTemplateCategoryList: React.FC = () => {
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
  } = useEmailTemplateCategories();

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
      createEmailTemplateCategoryColumns({
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
        title="Email Template Categories"
      >
        <EmailTemplateCategoryFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />

        <DataTable
          columns={columns}
          data={categories}
          error={error}
          loading={loading}
          noDataMessage="No email template categories found"
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

      <CreateEmailTemplateCategoryDrawer
        isOpen={showCreateDrawer}
        onClose={() => setShowCreateDrawer(false)}
        onSuccess={() => {
          refetchCategories();
        }}
      />

      {editId !== null && (
        <EditEmailTemplateCategoryDrawer
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

      <DeleteEmailTemplateCategoryModal
        categoryId={deleteId}
        isOpen={showDeleteModal}
        onClose={handleCloseDeleteModal}
        onSuccess={handleDeleteSuccess}
      />
    </>
  );
};

export default EmailTemplateCategoryList;
