import React, { useCallback, useEffect, useMemo, useState } from "react";
import MainPageWrapper from "@/components/MainPageWrapper/MainPageWrapper";
import { Button } from "@/components/Button/Button";
import { useNavigate } from "react-router-dom";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import DataTable from "@/components/Datatable/Datatable";
import {
  EventItem,
  fetchEventFilterMetadataAction,
  setPublishedEventAction,
} from "@/modules/eventRegistration/features/EventManagement/slices/eventListSlice";
import { useEvents } from "@/modules/eventRegistration/features/EventManagement/hooks/useEvents";
import { createEventColumns } from "@/modules/eventRegistration/features/EventManagement/components/EventColumns/EventColumns";
import EventFilters from "@/modules/eventRegistration/features/EventManagement/components/EventFilters/EventFilters";
import DeleteEventModal from "@/modules/eventRegistration/features/EventManagement/components/DeleteEventModal/DeleteEventModal";
import DuplicateEventModal from "@/modules/eventRegistration/features/EventManagement/components/DuplicateEventModal/DuplicateEventModal";

const EventList: React.FC = () => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();

  const { eventTypes, eventCategories, employeeRoles, trades, eventTags } =
    useAppSelector((state: RootState) => state.eventList);

  useEffect(() => {
    dispatch(fetchEventFilterMetadataAction());
  }, [dispatch]);

  const {
    events,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    sorting,
    handleFilterChange,
    handlePaginationChange,
    handleSortingChange,
    refetchEvents,
  } = useEvents();

  const handleCreateEvent = useCallback(() => {
    navigate("/event-registration/admin/events/new");
  }, [navigate]);

  const handleTogglePublished = useCallback(
    (uuid: string, newVal: boolean) => {
      dispatch(setPublishedEventAction(uuid, newVal));
    },
    [dispatch],
  );

  const [isDuplicateModalOpen, setDuplicateModalOpen] = useState(false);
  const [eventToDuplicate, setEventToDuplicate] = useState<number | null>(null);

  const openDuplicateModal = useCallback((eventId: number) => {
    setEventToDuplicate(eventId);
    setDuplicateModalOpen(true);
  }, []);

  const closeDuplicateModal = useCallback(() => {
    setDuplicateModalOpen(false);
    setEventToDuplicate(null);
  }, []);

  const handleDuplicateSuccess = useCallback(() => {
    closeDuplicateModal();
    refetchEvents();
  }, [closeDuplicateModal, refetchEvents]);

  const [isDeleteModalOpen, setDeleteModalOpen] = useState(false);
  const [eventToDelete, setEventToDelete] = useState<number | null>(null);

  const handleDeleteEvent = useCallback((id: number) => {
    setEventToDelete(id);
    setDeleteModalOpen(true);
  }, []);

  const handleCloseModal = useCallback(() => {
    setDeleteModalOpen(false);
    setEventToDelete(null);
  }, []);

  const handleViewSessions = useCallback(
    (uuid: string) => {
      navigate(`/event-registration/admin/events/${uuid}/sessions`);
    },
    [navigate],
  );

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

  const handleEditEvent = useCallback(
    (uuid: string) => {
      navigate(`/event-registration/admin/events/${uuid}/edit`);
    },
    [navigate],
  );

  const columns = useMemo(
    () =>
      createEventColumns({
        onTogglePublished: handleTogglePublished,
        onDuplicateEvent: openDuplicateModal,
        onEditEvent: handleEditEvent,
        onDeleteEvent: handleDeleteEvent,
        onViewSessions: handleViewSessions,
      }),
    [
      handleTogglePublished,
      openDuplicateModal,
      handleEditEvent,
      handleDeleteEvent,
      handleViewSessions,
    ],
  );

  return (
    <>
      <MainPageWrapper
        actions={<Button onClick={handleCreateEvent}>Create Event</Button>}
        error={error}
        title="Event Management"
      >
        <EventFilters
          employeeRoles={employeeRoles}
          eventCategories={eventCategories}
          eventTags={eventTags}
          eventTypes={eventTypes}
          filters={filters}
          onFilterChange={handleFiltersChange}
          trades={trades}
        />

        <DataTable<EventItem>
          columns={columns}
          data={events}
          error={error}
          loading={loading}
          noDataMessage="No events found"
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

      <DuplicateEventModal
        eventId={eventToDuplicate}
        isOpen={isDuplicateModalOpen}
        onClose={closeDuplicateModal}
        onSuccess={handleDuplicateSuccess}
      />

      <DeleteEventModal
        eventId={eventToDelete}
        isOpen={isDeleteModalOpen}
        onClose={handleCloseModal}
        onSuccess={handleCloseModal}
      />
    </>
  );
};

export default EventList;
