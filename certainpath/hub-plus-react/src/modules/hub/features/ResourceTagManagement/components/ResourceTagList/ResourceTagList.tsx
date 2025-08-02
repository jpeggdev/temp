import React, { useCallback, useMemo, useState } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import DataTable from "@/components/Datatable/Datatable";
import { useResourceTags } from "../../hooks/useResourceTags";
import ResourceTagFilters from "../ResourceTagFilters/ResourceTagFilters";
import CreateResourceTagDrawer from "../CreateResourceTagDrawer/CreateResourceTagDrawer";
import EditResourceTagDrawer from "../EditResourceTagDrawer/EditResourceTagDrawer";
import DeleteResourceTagModal from "../DeleteResourceTagModal/DeleteResourceTagModal";
import { createResourceTagColumns } from "@/modules/hub/features/ResourceTagManagement/components/CreateResourceTagColumns/CreateResourceTagColumns";

const ResourceTagList: React.FC = () => {
  const {
    tags,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    sorting,
    handleFilterChange,
    handlePaginationChange,
    handleSortingChange,
    refetchTags,
  } = useResourceTags();

  const [showCreateDrawer, setShowCreateDrawer] = useState(false);

  const [editId, setEditId] = useState<number | null>(null);
  const [showEditDrawer, setShowEditDrawer] = useState(false);

  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const handleCreateTag = useCallback(() => {
    setShowCreateDrawer(true);
  }, []);

  const handleEditTag = useCallback((id: number) => {
    setEditId(id);
    setShowEditDrawer(true);
  }, []);

  const handleDeleteTag = useCallback((id: number) => {
    setDeleteId(id);
    setShowDeleteModal(true);
  }, []);

  const handleDeleteSuccess = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
    refetchTags();
  }, [refetchTags]);

  const handleCloseDeleteModal = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
  }, []);

  const columns = useMemo(
    () =>
      createResourceTagColumns({
        onEditTag: handleEditTag,
        onDeleteTag: handleDeleteTag,
      }),
    [handleEditTag, handleDeleteTag],
  );

  return (
    <>
      <MainPageWrapper
        actions={<Button onClick={handleCreateTag}>Create Tag</Button>}
        error={error}
        title="Resource Tags"
      >
        <ResourceTagFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />

        <DataTable
          columns={columns}
          data={tags}
          error={error}
          loading={loading}
          noDataMessage="No resource tags found"
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

      <CreateResourceTagDrawer
        isOpen={showCreateDrawer}
        onClose={() => setShowCreateDrawer(false)}
        onSuccess={() => {
          refetchTags();
        }}
      />

      {editId !== null && (
        <EditResourceTagDrawer
          isOpen={showEditDrawer}
          onClose={() => {
            setShowEditDrawer(false);
            setEditId(null);
          }}
          onSuccess={() => {
            refetchTags();
          }}
          tagId={editId}
        />
      )}

      <DeleteResourceTagModal
        isOpen={showDeleteModal}
        onClose={handleCloseDeleteModal}
        onSuccess={handleDeleteSuccess}
        tagId={deleteId}
      />
    </>
  );
};

export default ResourceTagList;
