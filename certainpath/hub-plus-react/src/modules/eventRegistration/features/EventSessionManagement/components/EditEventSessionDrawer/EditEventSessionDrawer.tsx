import React, { Fragment, useEffect } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { useNotification } from "@/context/NotificationContext";
import EventSessionForm, {
  EventSessionFormValues,
} from "../EventSessionForm/EventSessionForm";
import EventSessionFormLoadingSkeleton from "../EventSessionFormLoadingSkeleton/EventSessionFormLoadingSkeleton";
import {
  fetchSingleEventSessionAction,
  updateEventSessionAction,
} from "../../slices/createUpdateEventSessionSlice";

interface EditEventSessionDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  sessionUuid: string | null;
  onSuccess?: () => void;
  eventUuid: string;
}

function EditEventSessionDrawer({
  isOpen,
  onClose,
  sessionUuid,
  eventUuid,
  onSuccess,
}: EditEventSessionDrawerProps) {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();

  const { loadingFetchSingle, fetchSingleError, singleSession, loadingUpdate } =
    useAppSelector((state: RootState) => state.createUpdateEventSession);

  useEffect(() => {
    if (isOpen && sessionUuid) {
      dispatch(fetchSingleEventSessionAction(sessionUuid));
    }
  }, [isOpen, sessionUuid, dispatch]);

  const handleSubmit = (values: EventSessionFormValues) => {
    if (!sessionUuid) return;

    const payload = {
      eventUuid,
      name: values.name,
      startDate: values.startDate,
      endDate: values.endDate,
      maxEnrollments: values.maxEnrollments,
      virtualLink: values.virtualLink || null,
      notes: values.notes || null,
      isPublished: values.isPublished,
      isVirtualOnly: values.isVirtualOnly,
      venueId: values.venueId?.id ?? null,
      timezoneId: values.timezoneId ?? null,
    };

    const finalData = {
      ...payload,
      instructorId: values.instructorId?.id ?? null,
    };

    dispatch(
      updateEventSessionAction(sessionUuid, finalData, () => {
        showNotification?.(
          "Success",
          "Event session updated successfully.",
          "success",
        );
        onClose();
        onSuccess?.();
      }),
    );
  };

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog as="div" className="relative z-40" onClose={onClose}>
        <Transition.Child
          as={Fragment}
          enter="transition-opacity ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="transition-opacity ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-black bg-opacity-30" />
        </Transition.Child>

        <div className="fixed inset-0 overflow-hidden">
          <div className="absolute inset-0 overflow-hidden">
            <div className="pointer-events-none fixed inset-y-0 right-0 flex max-w-full">
              <Transition.Child
                as={Fragment}
                enter="transform transition ease-in-out duration-300"
                enterFrom="translate-x-full"
                enterTo="translate-x-0"
                leave="transform transition ease-in-out duration-300"
                leaveFrom="translate-x-0"
                leaveTo="translate-x-full"
              >
                <Dialog.Panel className="pointer-events-auto w-screen max-w-md">
                  <div className="flex h-full flex-col bg-white shadow-xl">
                    <div className="flex items-center justify-between bg-primary px-4 py-4">
                      <Dialog.Title className="text-lg font-medium text-white">
                        Edit Event Session
                      </Dialog.Title>
                      <button
                        className="text-white"
                        onClick={onClose}
                        type="button"
                      >
                        <XMarkIcon className="h-6 w-6" />
                      </button>
                    </div>

                    <div className="flex-1 overflow-y-auto px-4 py-4 sm:px-6">
                      {fetchSingleError && (
                        <p className="mb-2 text-sm text-red-500">
                          {fetchSingleError}
                        </p>
                      )}

                      {loadingFetchSingle ? (
                        <EventSessionFormLoadingSkeleton />
                      ) : singleSession ? (
                        <EventSessionForm
                          initialData={{
                            name: singleSession.name || "",
                            startDate: singleSession.startDate || "",
                            endDate: singleSession.endDate || "",
                            maxEnrollments: singleSession.maxEnrollments ?? 0,
                            virtualLink: singleSession.virtualLink || "",
                            notes: singleSession.notes || "",
                            isPublished: singleSession.isPublished ?? false,
                            isVirtualOnly: singleSession.isVirtualOnly ?? false,
                            venueId:
                              singleSession.venueId != null
                                ? {
                                    id: singleSession.venueId,
                                    name:
                                      singleSession.venueName ??
                                      "Unknown venue",
                                  }
                                : null,
                            timezoneId: singleSession.timezoneId ?? 0,
                            instructorId:
                              singleSession.instructorId !== null
                                ? {
                                    id: singleSession.instructorId,
                                    name:
                                      singleSession.instructorName ??
                                      "Unknown Instructor",
                                  }
                                : null,
                          }}
                          loading={loadingUpdate}
                          onSubmit={handleSubmit}
                        />
                      ) : (
                        <p className="text-sm text-gray-500">
                          No session found or an error occurred.
                        </p>
                      )}
                    </div>
                  </div>
                </Dialog.Panel>
              </Transition.Child>
            </div>
          </div>
        </div>
      </Dialog>
    </Transition.Root>
  );
}

export default EditEventSessionDrawer;
