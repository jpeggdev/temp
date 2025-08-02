import React, { useCallback, useMemo, useState } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import DataTable from "@/components/Datatable/Datatable";
import { useEmployeeRoles } from "../../hooks/useEmployeeRoles";
import { createEmployeeRoleColumns } from "@/modules/hub/features/EmployeeRoleManagement/components/CreateEmployeeRoleColumns/CreateEmployeeRoleColumns";
import EmployeeRoleFilters from "@/modules/hub/features/EmployeeRoleManagement/components/EmployeeRoleFilters/EmployeeRoleFilters";
import CreateEmployeeRoleDrawer from "@/modules/hub/features/EmployeeRoleManagement/components/CreateEmployeeRoleDrawer/CreateEmployeeRoleDrawer";
import EditEmployeeRoleDrawer from "@/modules/hub/features/EmployeeRoleManagement/components/EditEmployeeRoleDrawer/EditEmployeeRoleDrawer";
import DeleteEmployeeRoleModal from "@/modules/hub/features/EmployeeRoleManagement/components/DeleteEmployeeRoleModal/DeleteEmployeeRoleModal";

const EmployeeRoleList: React.FC = () => {
  const {
    roles,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    sorting,
    handleFilterChange,
    handlePaginationChange,
    handleSortingChange,
    refetchRoles,
  } = useEmployeeRoles();

  const [showCreateDrawer, setShowCreateDrawer] = useState(false);
  const [editId, setEditId] = useState<number | null>(null);
  const [showEditDrawer, setShowEditDrawer] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const handleCreateRole = useCallback(() => {
    setShowCreateDrawer(true);
  }, []);

  const handleEditRole = useCallback((id: number) => {
    setEditId(id);
    setShowEditDrawer(true);
  }, []);

  const handleDeleteRole = useCallback((id: number) => {
    setDeleteId(id);
    setShowDeleteModal(true);
  }, []);

  const handleDeleteSuccess = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
    refetchRoles();
  }, [refetchRoles]);

  const handleCloseDeleteModal = useCallback(() => {
    setDeleteId(null);
    setShowDeleteModal(false);
  }, []);

  const columns = useMemo(
    () =>
      createEmployeeRoleColumns({
        onEditRole: handleEditRole,
        onDeleteRole: handleDeleteRole,
      }),
    [handleEditRole, handleDeleteRole],
  );

  return (
    <>
      <MainPageWrapper
        actions={<Button onClick={handleCreateRole}>Create Role</Button>}
        error={error}
        title="Employee Roles"
      >
        <EmployeeRoleFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />

        <DataTable
          columns={columns}
          data={roles}
          error={error}
          loading={loading}
          noDataMessage="No employee roles found"
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

      <CreateEmployeeRoleDrawer
        isOpen={showCreateDrawer}
        onClose={() => setShowCreateDrawer(false)}
        onSuccess={() => refetchRoles()}
      />

      {editId !== null && (
        <EditEmployeeRoleDrawer
          isOpen={showEditDrawer}
          onClose={() => {
            setShowEditDrawer(false);
            setEditId(null);
          }}
          onSuccess={() => refetchRoles()}
          roleId={editId}
        />
      )}

      <DeleteEmployeeRoleModal
        isOpen={showDeleteModal}
        onClose={handleCloseDeleteModal}
        onSuccess={handleDeleteSuccess}
        roleId={deleteId}
      />
    </>
  );
};

export default EmployeeRoleList;
