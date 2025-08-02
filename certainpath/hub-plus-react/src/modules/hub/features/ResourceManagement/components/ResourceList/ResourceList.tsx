import React, { useCallback, useEffect, useMemo, useState } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import { useNavigate } from "react-router-dom";
import { useResources } from "@/modules/hub/features/ResourceManagement/hooks/useResources/useResources";
import { createResourceColumns } from "@/modules/hub/features/ResourceManagement/components/ResourceColumns/ResourceColumns";
import ResourceFilters from "@/modules/hub/features/ResourceManagement/components/ResourceFilters/ResourceFilters";
import { ResourceItem } from "../../slices/resourceListSlice";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import {
  setPublishedResourceAction,
  setFeaturedResourceAction,
  getResourceFilterMetaDataAction,
} from "../../slices/resourceListSlice";
import { RootState } from "@/app/rootReducer";
import DeleteResourceModal from "@/modules/hub/features/ResourceManagement/components/DeleteResourceModal/DeleteResourceModal";
import DataTable from "@/components/Datatable/Datatable";

const ResourceList: React.FC = () => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();

  const { resourceTypes, employeeRoles, trades } = useAppSelector(
    (state: RootState) => state.resourceList,
  );

  useEffect(() => {
    dispatch(getResourceFilterMetaDataAction());
  }, [dispatch]);

  const {
    resources,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    sorting,
    handleFilterChange,
    handlePaginationChange,
    handleSortingChange,
  } = useResources();

  const handleCreateResource = useCallback(() => {
    navigate("/admin/resources/new");
  }, [navigate]);

  const handleTogglePublished = useCallback(
    (uuid: string, newVal: boolean) => {
      dispatch(setPublishedResourceAction(uuid, newVal));
    },
    [dispatch],
  );

  const handleToggleFeatured = useCallback(
    (uuid: string, newVal: boolean) => {
      dispatch(setFeaturedResourceAction(uuid, newVal));
    },
    [dispatch],
  );

  const [isDeleteModalOpen, setDeleteModalOpen] = useState(false);
  const [resourceToDelete, setResourceToDelete] = useState<string | null>(null);

  const handleDeleteResource = useCallback((uuid: string) => {
    setResourceToDelete(uuid);
    setDeleteModalOpen(true);
  }, []);

  const handleEditResource = useCallback(
    (uuid: string) => {
      navigate(`/admin/resources/${uuid}/edit`);
    },
    [navigate],
  );

  const handleCloseModal = useCallback(() => {
    setDeleteModalOpen(false);
    setResourceToDelete(null);
  }, []);

  const handleFiltersChange = useCallback(
    (filterKey: string, value: string | string[]) => {
      handleFilterChange(filterKey, value);
      handlePaginationChange({
        pageIndex: 0,
        pageSize: pagination.pageSize,
      });
    },
    [handleFilterChange, handlePaginationChange, pagination.pageSize],
  );

  const columns = useMemo(
    () =>
      createResourceColumns({
        onTogglePublished: handleTogglePublished,
        onToggleFeatured: handleToggleFeatured,
        onEditResource: handleEditResource,
        onDeleteResource: handleDeleteResource,
      }),
    [
      handleTogglePublished,
      handleToggleFeatured,
      handleEditResource,
      handleDeleteResource,
    ],
  );

  return (
    <>
      <MainPageWrapper
        actions={
          <Button onClick={handleCreateResource}>Create Resource</Button>
        }
        error={error}
        title="Resources"
      >
        <ResourceFilters
          employeeRoles={employeeRoles}
          filters={filters}
          onFilterChange={handleFiltersChange}
          resourceTypes={resourceTypes}
          trades={trades}
        />

        <DataTable<ResourceItem>
          columns={columns}
          data={resources}
          error={error}
          loading={loading}
          noDataMessage="No resources found"
          onPageChange={(newPageIndex, newPageSize) =>
            handlePaginationChange({
              pageIndex: newPageIndex,
              pageSize: newPageSize,
            })
          }
          onSortingChange={handleSortingChange}
          pageIndex={pagination.pageIndex}
          pageSize={pagination.pageSize}
          rowKeyExtractor={(item) => item.uuid}
          sorting={sorting}
          totalCount={totalCount}
        />
      </MainPageWrapper>

      <DeleteResourceModal
        isOpen={isDeleteModalOpen}
        onClose={handleCloseModal}
        onSuccess={handleCloseModal}
        resourceUuid={resourceToDelete}
      />
    </>
  );
};

export default ResourceList;
