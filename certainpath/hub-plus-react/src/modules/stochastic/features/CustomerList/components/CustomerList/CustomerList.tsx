import React, { useCallback, useMemo } from "react";
import { useDispatch } from "react-redux";
import { useStochasticCustomers } from "../../hooks/useStochasticCustomers";
import { createCustomersColumns } from "../CustomersColumns/CustomersColumns";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import CustomerListFilters from "../CustomerListFilters/CustomerListFilters";
import { StochasticCustomer } from "@/api/fetchStochasticCustomers/types";
import DataTable from "@/components/Datatable/Datatable";
import { updateCustomerDoNotMailAction } from "../../slices/stochasticCustomersSlice";

const CustomerList: React.FC = () => {
  const dispatch = useDispatch();
  const {
    customers,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    handlePaginationChange,
    handleFilterChange,
    handleSortingChange,
    filters,
  } = useStochasticCustomers();

  const handleToggleDoNotMail = useCallback(
    async (customerId: number, newValue: boolean) => {
      // Check if globally disabled first
      const customer = customers.find((c) => c.id === customerId);
      const isGlobalDoNotMail = customer?.address?.isGlobalDoNotMail ?? false;

      if (!isGlobalDoNotMail) {
        dispatch(updateCustomerDoNotMailAction(customerId, newValue));
      }
    },
    [dispatch, customers],
  );

  const columns = useMemo(
    () => createCustomersColumns({ onToggleDoNotMail: handleToggleDoNotMail }),
    [handleToggleDoNotMail],
  );

  const onFilterChange = useCallback(
    (searchTerm: string, isActive: number) => {
      handleFilterChange(searchTerm, isActive);
      handlePaginationChange({
        pageIndex: 0,
        pageSize: pagination.pageSize,
      });
    },
    [handleFilterChange, handlePaginationChange, pagination.pageSize],
  );

  return (
    <MainPageWrapper error={error} title="Customers">
      <CustomerListFilters filters={filters} onFilterChange={onFilterChange} />

      <div className="relative">
        <DataTable<StochasticCustomer>
          columns={columns}
          data={customers}
          error={error}
          loading={loading}
          noDataMessage="No customers found"
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
      </div>
    </MainPageWrapper>
  );
};

export default CustomerList;
