import React, { useCallback, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { useCompanies } from "../../hooks/useCompanies";
import { createCompaniesColumns } from "../CompanyColumns/CompanyColumns";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import CompanyListFilters from "../CompanyListFilters/CompanyListFilters";
import { Button } from "../../../../../../components/Button/Button";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { Company } from "../../slices/companiesSlice";
import DataTable from "@/components/Datatable/Datatable";

const CompanyList: React.FC = () => {
  const {
    companies,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    handlePaginationChange,
    handleFilterChange,
    handleSortingChange,
    filters,
  } = useCompanies();

  const navigate = useNavigate();

  const handleEdit = useCallback(
    (uuid: string) => {
      navigate(`/admin/companies/${uuid}/edit`);
    },
    [navigate],
  );

  const columns = useMemo(
    () => createCompaniesColumns({ handleEdit }),
    [handleEdit],
  );

  return (
    <MainPageWrapper
      actions={[
        <ShowIfHasAccess
          key="create-company"
          requiredPermissions={["CAN_CREATE_COMPANIES"]}
          requiredRoles={["ROLE_SUPER_ADMIN"]}
        >
          <Button onClick={() => navigate("/admin/companies/create")}>
            Create Company
          </Button>
        </ShowIfHasAccess>,
      ]}
      error={error}
      title="Companies"
    >
      <CompanyListFilters
        filters={filters}
        onFilterChange={(filterKey: string, value: string) => {
          handleFilterChange(filterKey, value);
          handlePaginationChange({
            pageIndex: 0,
            pageSize: pagination.pageSize,
          });
        }}
      />

      <div className="relative">
        <DataTable<Company>
          columns={columns}
          data={companies}
          error={error}
          loading={loading}
          noDataMessage="No companies found"
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

export default CompanyList;
