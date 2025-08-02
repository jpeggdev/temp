import React, { Fragment } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import EmployeeRoleForm, {
  EmployeeRoleFormValues,
} from "../EmployeeRoleForm/EmployeeRoleForm";
import { useNotification } from "@/context/NotificationContext";
import { createEmployeeRoleAction } from "@/modules/hub/features/EmployeeRoleManagement/slice/employeeRoleSlice";

interface CreateEmployeeRoleDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
}

const CreateEmployeeRoleDrawer: React.FC<CreateEmployeeRoleDrawerProps> = ({
  isOpen,
  onClose,
  onSuccess,
}) => {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();

  const { loadingCreate } = useAppSelector(
    (state: RootState) => state.employeeRole,
  );

  const handleSubmit = (values: EmployeeRoleFormValues) => {
    const payload = { name: values.name };

    dispatch(
      createEmployeeRoleAction(payload, () => {
        showNotification?.(
          "Success",
          "Employee role created successfully.",
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
                    {/* Header */}
                    <div className="flex items-center justify-between px-4 py-4 bg-primary">
                      <Dialog.Title className="text-lg font-medium text-white">
                        Create Employee Role
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
                      <EmployeeRoleForm
                        initialData={{ name: "" }}
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

export default CreateEmployeeRoleDrawer;
