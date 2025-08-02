import React, { useCallback, useMemo, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import { useAppDispatch } from "@/app/hooks";
import DataTable from "@/components/Datatable/Datatable";
import { useEventSessions } from "@/modules/eventRegistration/features/EventSessionManagement/hooks/useEventSessions";
import EventSessionFilters from "@/modules/eventRegistration/features/EventSessionManagement/components/EventSessionFilters/EventSessionFilters";
import DeleteEventSessionModal from "@/modules/eventRegistration/features/EventSessionManagement/components/DeleteEventSessionModal/DeleteEventSessionModal";
import CreateEventSessionDrawer from "@/modules/eventRegistration/features/EventSessionManagement/components/CreateEventSessionDrawer/CreateEventSessionDrawer";
import EditEventSessionDrawer from "@/modules/eventRegistration/features/EventSessionManagement/components/EditEventSessionDrawer/EditEventSessionDrawer";
import { setPublishedEventSessionAction } from "@/modules/eventRegistration/features/EventSessionManagement/slices/eventSessionListSlice";
import { createEventSessionColumns } from "@/modules/eventRegistration/features/EventSessionManagement/components/EventSessionColumns/EventSessionColumns";

function EventSessionList() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate(); // For redirecting to waitlist page
  const { uuid: eventUuid } = useParams();

  const {
    sessions,
    totalCount,
    loading,
    error,
    pagination,
    sorting,
    filters,
    handleFilterChange,
    handlePaginationChange,
    handleSortingChange,
    refetchSessions,
    eventName,
  } = useEventSessions(eventUuid);

  const [isCreateDrawerOpen, setCreateDrawerOpen] = useState(false);
  const openCreateDrawer = useCallback(() => {
    setCreateDrawerOpen(true);
  }, []);
  const closeCreateDrawer = useCallback(() => {
    setCreateDrawerOpen(false);
  }, []);

  const [isEditDrawerOpen, setEditDrawerOpen] = useState(false);
  const [sessionToEdit, setSessionToEdit] = useState<string | null>(null);
  const openEditDrawer = useCallback((uuid: string) => {
    setSessionToEdit(uuid);
    setEditDrawerOpen(true);
  }, []);
  const closeEditDrawer = useCallback(() => {
    setEditDrawerOpen(false);
    setSessionToEdit(null);
  }, []);

  const [isDeleteModalOpen, setDeleteModalOpen] = useState(false);
  const [sessionToDelete, setSessionToDelete] = useState<string | null>(null);

  const handleDeleteSession = useCallback((uuid: string) => {
    setSessionToDelete(uuid);
    setDeleteModalOpen(true);
  }, []);

  const handleCloseModal = useCallback(() => {
    setDeleteModalOpen(false);
    setSessionToDelete(null);
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

  const handleTogglePublishedSession = useCallback(
    (sessionUuid: string, newVal: boolean) => {
      dispatch(setPublishedEventSessionAction(sessionUuid, newVal));
    },
    [dispatch],
  );

  const handleWaitlistSession = useCallback(
    (sessionUuid: string) => {
      navigate(
        `/event-registration/admin/events/${eventUuid}/sessions/${sessionUuid}/waitlist`,
      );
    },
    [navigate, eventUuid],
  );

  const columns = useMemo(
    () =>
      createEventSessionColumns({
        onDeleteSession: handleDeleteSession,
        onEditSession: openEditDrawer,
        onTogglePublishedSession: handleTogglePublishedSession,
        onWaitlistSession: handleWaitlistSession, // PASS IT HERE
      }),
    [
      handleDeleteSession,
      openEditDrawer,
      handleTogglePublishedSession,
      handleWaitlistSession,
    ],
  );

  const manualBreadcrumbs = useMemo(() => {
    if (!eventUuid) return undefined;
    const titleOrFallback = eventName || `Event ${eventUuid}`;
    return [
      { path: "/event-registration/admin/events/", label: "Event Management" },
      {
        path: "",
        label: `Event Sessions (${titleOrFallback})`,
        clickable: false,
      },
    ];
  }, [eventUuid, eventName]);

  return (
    <>
      <MainPageWrapper
        actions={<Button onClick={openCreateDrawer}>Create Session</Button>}
        error={error}
        manualBreadcrumbs={manualBreadcrumbs}
        title="Event Sessions"
      >
        <EventSessionFilters
          filters={filters}
          onFilterChange={handleFiltersChange}
        />

        <DataTable
          columns={columns}
          data={sessions}
          error={error}
          loading={loading}
          noDataMessage="No sessions found"
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

      {eventUuid && (
        <CreateEventSessionDrawer
          eventUuid={eventUuid}
          isOpen={isCreateDrawerOpen}
          onClose={closeCreateDrawer}
          onSuccess={() => {
            closeCreateDrawer();
            refetchSessions();
          }}
        />
      )}

      {eventUuid && (
        <EditEventSessionDrawer
          eventUuid={eventUuid}
          isOpen={isEditDrawerOpen}
          onClose={closeEditDrawer}
          onSuccess={() => {
            closeEditDrawer();
            refetchSessions();
          }}
          sessionUuid={sessionToEdit}
        />
      )}

      <DeleteEventSessionModal
        isOpen={isDeleteModalOpen}
        onClose={handleCloseModal}
        onSuccess={() => {
          handleCloseModal();
          refetchSessions();
        }}
        sessionUuid={sessionToDelete}
      />
    </>
  );
}

export default EventSessionList;
