import React, { Fragment, ReactNode } from "react";
import { Dialog, Transition } from "@headlessui/react";

interface DeleteConfirmationModalProps {
  isOpen: boolean;
  onConfirm: () => void;
  onCancel: () => void;
  loading?: boolean;
  error?: string | null;
  children?: ReactNode;
}

const DeleteConfirmationModal: React.FC<DeleteConfirmationModalProps> = ({
  isOpen,
  onConfirm,
  onCancel,
  loading = false,
  error,
  children,
}) => {
  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog as="div" className="relative z-40" onClose={onCancel}>
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

        <div className="fixed inset-0 z-10 overflow-y-auto">
          <div className="flex min-h-full items-center justify-center p-4 text-center">
            <Transition.Child
              as={Fragment}
              enter="transform transition ease-out duration-300"
              enterFrom="scale-95"
              enterTo="scale-100"
              leave="transform transition ease-in duration-200"
              leaveFrom="scale-100"
              leaveTo="scale-95"
            >
              <Dialog.Panel className="w-full max-w-md transform rounded-lg bg-white p-6 text-left shadow-xl transition-all">
                <Dialog.Title className="text-lg font-medium leading-6 text-gray-900">
                  Confirm Delete
                </Dialog.Title>
                <div className="mt-3 text-sm text-gray-700">
                  {children ?? (
                    <p>
                      Are you sure you want to delete this record? This cannot
                      be undone.
                    </p>
                  )}
                </div>

                {error && (
                  <div className="mt-3 text-sm text-red-600">{error}</div>
                )}

                <div className="mt-5 flex justify-end space-x-3">
                  <button
                    className="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    onClick={onCancel}
                    type="button"
                  >
                    Cancel
                  </button>
                  <button
                    className={`rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm ${
                      loading
                        ? "bg-gray-300 cursor-not-allowed"
                        : "bg-red-600 hover:bg-red-700"
                    }`}
                    disabled={loading}
                    onClick={onConfirm}
                    type="button"
                  >
                    {loading ? "Deleting..." : "Delete"}
                  </button>
                </div>
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </div>
      </Dialog>
    </Transition.Root>
  );
};

export default DeleteConfirmationModal;
