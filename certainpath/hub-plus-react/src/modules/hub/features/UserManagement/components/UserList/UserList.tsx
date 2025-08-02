import React, { useCallback, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { useUsers } from "../../hooks/useUsers";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import UserListFilters from "../UserListFilters/UserListFilters";
import { Button } from "@/components/Button/Button";
import ShowIfHasAccess from "@/components/ShowIfHasAccess/ShowIfHasAccess";
import { User } from "../../slices/usersSlice";
import { createUsersColumns } from "../UserColumns/UserColumns";
import DataTable from "@/components/Datatable/Datatable";

const UserList: React.FC = () => {
  const {
    users,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    handlePaginationChange,
    handleSortingChange,
    handleFilterChange,
    filters,
  } = useUsers();

  const navigate = useNavigate();

  const handleEdit = useCallback(
    (uuid: string) => {
      navigate(`/hub/users/${uuid}/edit`);
    },
    [navigate],
  );

  const handleImpersonateUser = useCallback(
    (uuid: string) => {
      localStorage.removeItem("selectedCompanyUuid");
      localStorage.setItem("impersonateUserUuid", uuid);
      console.log(`Switched to user with UUID: ${uuid}`);
      navigate(0);
    },
    [navigate],
  );

  // Make sure at least one column has { enableSorting: true }
  const columns = useMemo(
    () => createUsersColumns({ handleEdit, handleImpersonateUser }),
    [handleEdit, handleImpersonateUser],
  );

  return (
    <MainPageWrapper
      actions={
        <ShowIfHasAccess
          requiredPermissions={["CAN_CREATE_COMPANIES"]}
          requiredRoles={["ROLE_SUPER_ADMIN"]}
        >
          <Button onClick={() => navigate("/hub/users/create")}>
            Create User
          </Button>
        </ShowIfHasAccess>
      }
      error={error}
      title="Users"
    >
      <UserListFilters
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
        <DataTable<User>
          columns={columns}
          data={users}
          error={error}
          loading={loading}
          noDataMessage="No users found"
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

export default UserList;
