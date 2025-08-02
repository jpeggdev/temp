import React, { useMemo } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Button } from "@/components/Button/Button";
import { useNavigate } from "react-router-dom";
import DataTable from "@/components/Datatable/Datatable";
import { voucherColumns } from "@/modules/eventRegistration/features/EventVoucherManagement/components/VoucherColumns/VoucherColumns";
import { Voucher } from "@/modules/eventRegistration/features/EventVoucherManagement/api/createVoucher/types";
import { useVoucherList } from "@/modules/eventRegistration/features/EventVoucherManagement/hooks/useVoucherList";
import VoucherListFilters from "@/modules/eventRegistration/features/EventVoucherManagement/components/VoucherListFilters/VoucherListFilters";
import { useDeleteVoucher } from "@/modules/eventRegistration/features/EventVoucherManagement/hooks/useDeleteVoucher";
import DeleteVoucherModal from "@/modules/eventRegistration/features/EventVoucherManagement/components/DeleteVoucherModal/DeleteVoucherModal";

const VoucherList: React.FC = () => {
  const navigate = useNavigate();

  const {
    vouchers,
    loading,
    error,
    totalCount,
    filters,
    pagination,
    refetchVouchers,
    handleEditVoucher,
    handleFilterChange,
    handleSortingChange,
    handlePaginationChange,
  } = useVoucherList();

  const {
    isDeleting,
    showDeleteModal,
    handleShowDeleteModal,
    handleCloseDeleteModal,
    handleDelete,
  } = useDeleteVoucher({ refetchVouchers });

  const columns = useMemo(
    () =>
      voucherColumns({
        handleEditVoucher,
        handleShowDeleteModal,
      }),
    [],
  );

  return (
    <MainPageWrapper
      actions={
        <ShowIfHasAccess requiredRoles={["ROLE_SUPER_ADMIN"]}>
          <Button
            disabled={false}
            onClick={() => {
              navigate("/event-registration/admin/voucher/new");
            }}
          >
            New Voucher
          </Button>
        </ShowIfHasAccess>
      }
      error={error}
      title="Vouchers"
    >
      <VoucherListFilters
        filters={filters}
        onFilterChange={handleFilterChange}
      />

      <div className="relative">
        <DataTable<Voucher>
          columns={columns}
          data={vouchers}
          error={error}
          loading={loading}
          noDataMessage="No vouchers found"
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

      <DeleteVoucherModal
        handleDelete={handleDelete}
        isDeleting={isDeleting}
        isOpen={showDeleteModal}
        onClose={handleCloseDeleteModal}
      />
    </MainPageWrapper>
  );
};

export default VoucherList;
