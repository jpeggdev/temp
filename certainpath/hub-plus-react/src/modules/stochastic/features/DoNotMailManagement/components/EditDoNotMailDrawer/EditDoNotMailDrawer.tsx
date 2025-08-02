import React, { Fragment, useEffect } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "@/app/rootReducer";
import {
  fetchSingleRestrictedAddressAction,
  resetFetchSingleRestrictedAddress,
} from "../../slices/fetchSingleRestrictedAddressSlice";
import {
  updateRestrictedAddressAction,
  resetUpdateRestrictedAddress,
} from "../../slices/updateRestrictedAddressSlice";
import { fetchRestrictedAddressesAction } from "../../slices/fetchRestrictedAddressesSlice";
import DoNotMailForm, {
  DoNotMailFormValues,
} from "../DoNotMailForm/DoNotMailForm";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

// 1) Import the notification hook
import { useNotification } from "@/context/NotificationContext";

interface EditDoNotMailDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  addressId: number; // ID for fetching & editing
}

const EditDoNotMailDrawer: React.FC<EditDoNotMailDrawerProps> = ({
  isOpen,
  onClose,
  addressId,
}) => {
  const dispatch = useDispatch();

  const {
    restrictedAddress,
    loading: loadingFetch,
    error: errorFetch,
  } = useSelector((state: RootState) => state.fetchSingleRestrictedAddress);

  const {
    updatedAddress,
    loading: loadingUpdate,
    error: errorUpdate,
  } = useSelector((state: RootState) => state.updateRestrictedAddress);

  // 2) Destructure showNotification from your hook
  const { showNotification } = useNotification();

  // Fetch the single address on open
  useEffect(() => {
    if (isOpen) {
      dispatch(fetchSingleRestrictedAddressAction(addressId));
    }
  }, [isOpen, addressId, dispatch]);

  // Reset slices on unmount
  useEffect(() => {
    return () => {
      dispatch(resetFetchSingleRestrictedAddress());
      dispatch(resetUpdateRestrictedAddress());
    };
  }, [dispatch]);

  // If update succeeded, show success, close & refetch
  useEffect(() => {
    if (updatedAddress) {
      // Show success notification
      showNotification(
        "Success",
        "Restricted address updated successfully.",
        "success",
      );

      onClose();
      dispatch(resetUpdateRestrictedAddress());

      // refetch the table data
      dispatch(fetchRestrictedAddressesAction({ page: 1, perPage: 10 }));
    }
  }, [updatedAddress, onClose, dispatch, showNotification]);

  const handleEditSubmit = (values: DoNotMailFormValues) => {
    const updatePayload = {
      address1: values.address1,
      address2: values.address2 || null,
      city: values.city,
      stateCode: values.stateCode,
      postalCode: values.postalCode,
      countryCode: values.countryCode,
    };
    dispatch(updateRestrictedAddressAction(addressId, updatePayload));
  };

  const renderSkeletonForm = () => {
    return (
      <div className="flex flex-col space-y-8">
        <Skeleton height={38} />
        <Skeleton height={38} />
        <Skeleton height={38} />
        <Skeleton height={38} />
        <Skeleton height={38} />
        <Skeleton height={38} />
      </div>
    );
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
                        Edit Restricted Address
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
                      {/* Errors */}
                      {errorFetch && (
                        <p className="text-sm text-red-500 mb-2">
                          {errorFetch}
                        </p>
                      )}
                      {errorUpdate && (
                        <p className="text-sm text-red-500 mb-2">
                          {errorUpdate}
                        </p>
                      )}

                      {loadingFetch ? (
                        renderSkeletonForm()
                      ) : restrictedAddress ? (
                        <DoNotMailForm
                          initialData={{
                            address1: restrictedAddress.address1,
                            address2: restrictedAddress.address2 ?? "",
                            city: restrictedAddress.city,
                            stateCode: restrictedAddress.stateCode,
                            postalCode: restrictedAddress.postalCode,
                            countryCode: restrictedAddress.countryCode,
                          }}
                          loading={loadingUpdate}
                          onSubmit={handleEditSubmit}
                        />
                      ) : (
                        <p className="text-sm text-gray-500">
                          No address found or error occurred.
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

export default EditDoNotMailDrawer;
