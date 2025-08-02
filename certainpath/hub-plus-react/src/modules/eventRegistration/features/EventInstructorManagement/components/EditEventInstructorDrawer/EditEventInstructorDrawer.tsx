import React, { Fragment, useEffect } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { useNotification } from "@/context/NotificationContext";
import {
  getEventInstructorAction,
  updateEventInstructorAction,
} from "@/modules/eventRegistration/features/EventInstructorManagement/slices/eventInstructorSlice";
import EventInstructorFormLoadingSkeleton from "@/modules/eventRegistration/features/EventInstructorManagement/components/EventInstructorFormLoadingSkeleton/EventInstructorFormLoadingSkeleton";
import EventInstructorForm, {
  EventInstructorFormValues,
} from "@/modules/eventRegistration/features/EventInstructorManagement/components/EventInstructorForm/EventInstructorForm";

interface EditEventInstructorDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  instructorId: number;
  onSuccess?: () => void;
}

const EditEventInstructorDrawer: React.FC<EditEventInstructorDrawerProps> = ({
  isOpen,
  onClose,
  instructorId,
  onSuccess,
}) => {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();

  const {
    detailsLoading,
    detailsError,
    selectedInstructor,
    updateLoading,
    updateError,
  } = useAppSelector((state: RootState) => state.eventInstructor);

  useEffect(() => {
    if (isOpen && instructorId) {
      dispatch(getEventInstructorAction(instructorId));
    }
  }, [isOpen, instructorId, dispatch]);

  const handleSubmit = (values: EventInstructorFormValues) => {
    const payload = {
      name: values.name,
      email: values.email,
      phone: values.phone || null,
    };

    dispatch(
      updateEventInstructorAction(instructorId, payload, () => {
        showNotification?.(
          "Success",
          "Instructor updated successfully.",
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
                    <div className="flex items-center justify-between px-4 py-4 bg-primary">
                      <Dialog.Title className="text-lg font-medium text-white">
                        Edit Instructor
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
                      {detailsError && (
                        <p className="text-sm text-red-500 mb-2">
                          {detailsError}
                        </p>
                      )}

                      {updateError && (
                        <p className="text-sm text-red-500 mb-2">
                          {updateError}
                        </p>
                      )}

                      {detailsLoading ? (
                        <EventInstructorFormLoadingSkeleton />
                      ) : selectedInstructor ? (
                        <EventInstructorForm
                          initialData={{
                            name: selectedInstructor.name,
                            email: selectedInstructor.email,
                            phone: selectedInstructor.phone || "",
                          }}
                          loading={updateLoading}
                          onSubmit={handleSubmit}
                        />
                      ) : (
                        <p className="text-sm text-gray-500">
                          No instructor found or an error occurred.
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
};

export default EditEventInstructorDrawer;
