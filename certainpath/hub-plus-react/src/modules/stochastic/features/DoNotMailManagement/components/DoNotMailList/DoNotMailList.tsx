import React, { useCallback, useMemo, useState, useEffect } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import { useNotification } from "@/context/NotificationContext";
import { useDoNotMail } from "../../hooks/useDoNotMail";
import { createDoNotMailColumns } from "../DoNotMailColumns/DoNotMailColumns";
import DoNotMailFilters from "../DoNotMailFilters/DoNotMailFilters";
import CreateDoNotMailDrawer from "../CreateDoNotMailDrawer/CreateDoNotMailDrawer";
import EditDoNotMailDrawer from "../EditDoNotMailDrawer/EditDoNotMailDrawer";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import {
  deleteRestrictedAddressAction,
  setDeleteSuccess,
} from "../../slices/deleteRestrictedAddressSlice";
import { fetchRestrictedAddressesAction } from "../../slices/fetchRestrictedAddressesSlice";
import DeleteConfirmationModal from "../DeleteConfirmationModal/DeleteConfirmationModal";
import { RestrictedAddress } from "@/api/fetchRestrictedAddresses/types";
import DataTable from "@/components/Datatable/Datatable";

const DoNotMailList: React.FC = () => {
  const dispatch = useDispatch();
  const { showNotification } = useNotification();

  const {
    addresses,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    handlePaginationChange,
    handleFilterChange,
    sorting,
    handleSortingChange,
  } = useDoNotMail();

  const [showCreateDrawer, setShowCreateDrawer] = useState(false);
  const [editId, setEditId] = useState<number | null>(null);
  const [showEditDrawer, setShowEditDrawer] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

  const {
    loading: deleteLoading,
    error: deleteError,
    success,
  } = useSelector((state: RootState) => state.deleteRestrictedAddress);

  useEffect(() => {
    if (success) {
      setShowDeleteConfirm(false);
      setDeleteId(null);
      dispatch(setDeleteSuccess(false));
      showNotification(
        "Deleted",
        "Restricted address deleted successfully.",
        "success",
      );
      dispatch(fetchRestrictedAddressesAction({ page: 1, perPage: 10 }));
    }
  }, [success, dispatch, showNotification]);

  const handleEditAddress = useCallback((id: number) => {
    setEditId(id);
    setShowEditDrawer(true);
  }, []);

  const handleDeleteAddress = useCallback((id: number) => {
    setDeleteId(id);
    setShowDeleteConfirm(true);
  }, []);

  const handleConfirmDelete = useCallback(() => {
    if (!deleteId) return;
    dispatch(deleteRestrictedAddressAction(deleteId));
  }, [deleteId, dispatch]);

  const handleCancelDelete = useCallback(() => {
    setShowDeleteConfirm(false);
    setDeleteId(null);
  }, []);

  const columns = useMemo(
    () => createDoNotMailColumns({ handleEditAddress, handleDeleteAddress }),
    [handleEditAddress, handleDeleteAddress],
  );

  return (
    <>
      <MainPageWrapper
        actions={
          <Button onClick={() => setShowCreateDrawer(true)}>
            Create Restricted Address
          </Button>
        }
        error={error}
        title="Do Not Mail List"
      >
        <DoNotMailFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />

        <DataTable<RestrictedAddress>
          columns={columns}
          data={addresses}
          error={error}
          loading={loading}
          noDataMessage="No restricted addresses found"
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
      </MainPageWrapper>

      <CreateDoNotMailDrawer
        isOpen={showCreateDrawer}
        onClose={() => setShowCreateDrawer(false)}
      />

      {editId !== null && (
        <EditDoNotMailDrawer
          addressId={editId}
          isOpen={showEditDrawer}
          onClose={() => {
            setShowEditDrawer(false);
            setEditId(null);
          }}
        />
      )}

      <DeleteConfirmationModal
        error={deleteError}
        isOpen={showDeleteConfirm}
        loading={deleteLoading}
        onCancel={handleCancelDelete}
        onConfirm={handleConfirmDelete}
      >
        <p className="mt-2 text-sm text-gray-700">
          Are you sure you want to delete this restricted address? This action
          cannot be undone.
        </p>
      </DeleteConfirmationModal>
    </>
  );
};

export default DoNotMailList;
