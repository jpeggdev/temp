import React, { useMemo } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Button } from "@/components/Button/Button";
import { useNavigate } from "react-router-dom";
import DataTable from "@/components/Datatable/Datatable";
import { discountColumns } from "@/modules/eventRegistration/features/EventDiscountManagement/components/DiscountColumns/DiscountColumns";
import { useDiscountList } from "@/modules/eventRegistration/features/EventDiscountManagement/hooks/useDiscountList";
import { Discount } from "@/modules/eventRegistration/features/EventDiscountManagement/api/createDiscount/types";
import DiscountListFilters from "@/modules/eventRegistration/features/EventDiscountManagement/components/DiscountListFilters/DiscountListFilters";

const DiscountList: React.FC = () => {
  const navigate = useNavigate();

  const {
    discounts,
    loading,
    error,
    totalCount,
    filters,
    pagination,
    handleFilterChange,
    handleEditDiscount,
    handleSortingChange,
    handlePaginationChange,
  } = useDiscountList();

  const columns = useMemo(
    () =>
      discountColumns({
        handleEditDiscount,
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
                navigate("/event-registration/admin/discount/new");
              }}
            >
              New Discount
            </Button>
          </ShowIfHasAccess>
        }
        error={error}
        title="Discounts"
      >
        <DiscountListFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />

        <div className="relative">
          <DataTable<Discount>
            columns={columns}
            data={discounts}
            error={error}
            loading={loading}
            noDataMessage="No discounts found"
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
            totalCount={totalCount || 0}
          />
        </div>
      </MainPageWrapper>
    </>
  );
};

export default DiscountList;
