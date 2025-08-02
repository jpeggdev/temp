import React, { Fragment } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { useNotification } from "@/context/NotificationContext";
import LocationForm, {
  LocationFormValues,
} from "@/modules/stochastic/features/LocationsList/components/LocationForm/LocationForm";
import { createLocationAction } from "@/modules/stochastic/features/LocationsList/slices/locationSlice";

interface CreateLocationDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
}

const CreateLocationDrawer: React.FC<CreateLocationDrawerProps> = ({
  isOpen,
  onClose,
  onSuccess,
}) => {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();

  const { loadingCreate } = useAppSelector(
    (state: RootState) => state.emailTemplateCategory,
  );

  const handleSubmit = (values: LocationFormValues) => {
    dispatch(
      createLocationAction(values, () => {
        showNotification?.(
          "Success",
          "Location created successfully.",
          "success",
        );
        onClose();
        onSuccess?.();
      }),
    );
  };

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog className="relative z-40" onClose={onClose}>
        <Transition.Child>
          <div className="fixed inset-0 bg-black bg-opacity-30" />
        </Transition.Child>

        <div className="fixed inset-0 overflow-hidden">
          <div className="absolute inset-0 overflow-hidden">
            <div className="pointer-events-none fixed inset-y-0 right-0 flex max-w-full">
              <Transition.Child>
                <Dialog.Panel className="pointer-events-auto w-screen max-w-md">
                  <div className="flex h-full flex-col bg-white shadow-xl">
                    <div className="flex items-center justify-between px-4 py-4 bg-primary">
                      <Dialog.Title className="text-lg font-medium text-white">
                        Create Location
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
                      <LocationForm
                        initialData={{
                          name: "",
                          description: "",
                          postalCodes: [],
                        }}
                        loading={loadingCreate}
                        onSubmit={handleSubmit}
                      />
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

export default CreateLocationDrawer;
