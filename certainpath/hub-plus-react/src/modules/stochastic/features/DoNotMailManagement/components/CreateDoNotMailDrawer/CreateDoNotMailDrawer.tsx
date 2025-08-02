import React, { Fragment, useEffect } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import {
  createRestrictedAddressAction,
  resetCreateRestrictedAddress,
} from "../../slices/createRestrictedAddressSlice";
import { fetchRestrictedAddressesAction } from "../../slices/fetchRestrictedAddressesSlice"; // for refetch
import DoNotMailForm, {
  DoNotMailFormValues,
} from "../DoNotMailForm/DoNotMailForm";

// 1) Import the notification hook
import { useNotification } from "@/context/NotificationContext";

interface CreateDoNotMailDrawerProps {
  isOpen: boolean;
  onClose: () => void;
}

const CreateDoNotMailDrawer: React.FC<CreateDoNotMailDrawerProps> = ({
  isOpen,
  onClose,
}) => {
  const dispatch = useDispatch();
  const { loading, error, newAddress } = useSelector(
    (state: RootState) => state.createRestrictedAddress,
  );

  // 2) Destructure the showNotification function from the hook
  const { showNotification } = useNotification();

  // If we've successfully created a new address, close the drawer, show success, & refetch
  useEffect(() => {
    if (newAddress) {
      // Show success notification
      showNotification(
        "Success",
        "Restricted address created successfully.",
        "success",
      );

      // close the drawer
      onClose();

      // reset slice so subsequent creations start fresh
      dispatch(resetCreateRestrictedAddress());

      // refetch the entire list
      dispatch(fetchRestrictedAddressesAction({ page: 1, perPage: 10 }));
    }
  }, [newAddress, onClose, dispatch, showNotification]);

  // Clear slice on unmount, so subsequent opens start fresh
  useEffect(() => {
    return () => {
      dispatch(resetCreateRestrictedAddress());
    };
  }, [dispatch]);

  // 3) Handle the form submission
  const handleSubmit = (values: DoNotMailFormValues) => {
    const payload = {
      address1: values.address1,
      address2: values.address2 || null,
      city: values.city,
      stateCode: values.stateCode,
      postalCode: values.postalCode,
      countryCode: values.countryCode,
    };
    dispatch(createRestrictedAddressAction(payload));
  };

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog className="relative z-40" onClose={onClose}>
        {/* Overlay */}
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
            {/* Drawer panel */}
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
                    {/* Header */}
                    <div className="flex items-center justify-between px-4 py-4 bg-primary dark:bg-secondary">
                      <Dialog.Title className="text-lg font-medium text-white">
                        Create Restricted Address
                      </Dialog.Title>
                      <button
                        className="text-white"
                        onClick={onClose}
                        type="button"
                      >
                        <XMarkIcon className="h-6 w-6" />
                      </button>
                    </div>

                    {/* Body */}
                    <div className="flex-1 overflow-y-auto px-4 py-4 sm:px-6">
                      {error && (
                        <p className="text-sm text-red-500 mb-2">{error}</p>
                      )}
                      <DoNotMailForm
                        initialData={{
                          address1: "",
                          address2: "",
                          city: "",
                          stateCode: "",
                          postalCode: "",
                          countryCode: "",
                        }}
                        loading={loading}
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

export default CreateDoNotMailDrawer;
