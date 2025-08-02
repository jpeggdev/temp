import React, { useState, useEffect, Fragment } from "react";
import {
  Transition,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { Button } from "@/components/Button/Button";
import {
  BaseEntity,
  CreateEntityFn,
} from "@/components/EntityPickerModal/EntityPickerModal";

export interface CreateEntityDrawerProps<T extends BaseEntity> {
  isOpen: boolean;
  onClose: () => void;
  createEntity: CreateEntityFn<T>;
  onEntityCreated: (newEntity: T) => void;
  entityNameSingular?: string;
}

export default function CreateEntityDrawer<T extends BaseEntity>({
  isOpen,
  onClose,
  createEntity,
  onEntityCreated,
  entityNameSingular = "Entity",
}: CreateEntityDrawerProps<T>) {
  const [name, setName] = useState("");
  const [isCreating, setIsCreating] = useState(false);

  useEffect(() => {
    if (isOpen) {
      setName("");
      setIsCreating(false);
    }
  }, [isOpen]);

  const handleClose = () => {
    onClose();
  };

  const handleCreate = async () => {
    if (!name.trim()) return;
    setIsCreating(true);
    try {
      const newEntity = await createEntity({ name });
      onEntityCreated(newEntity);
      onClose();
    } catch (error) {
      console.error(`Failed to create ${entityNameSingular}:`, error);
    } finally {
      setIsCreating(false);
    }
  };

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog as="div" className="relative z-[10000]" onClose={handleClose}>
        <Transition.Child
          as={Fragment}
          enter="transition-opacity ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="transition-opacity ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-black bg-opacity-30 z-[10000]" />
        </Transition.Child>
        <div className="fixed inset-0 overflow-hidden z-[10000]">
          <div className="absolute inset-0 overflow-hidden">
            <div className="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
              <Transition.Child
                as={Fragment}
                enter="transform transition ease-in-out duration-500"
                enterFrom="translate-x-full"
                enterTo="translate-x-0"
                leave="transform transition ease-in-out duration-500"
                leaveFrom="translate-x-0"
                leaveTo="translate-x-full"
              >
                <DialogPanel className="pointer-events-auto w-screen max-w-md">
                  <div className="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl">
                    <div className="flex-1 overflow-y-auto">
                      <div className="bg-primary px-4 py-6 sm:px-6">
                        <div className="flex items-center justify-between">
                          <DialogTitle className="text-base font-semibold text-white">
                            Create New {entityNameSingular}
                          </DialogTitle>
                          <button
                            className="relative rounded-md text-white hover:text-white focus:outline-none focus:ring-2 focus:ring-white"
                            onClick={handleClose}
                            type="button"
                          >
                            <XMarkIcon aria-hidden="true" className="h-6 w-6" />
                          </button>
                        </div>
                        <p className="text-sm text-white mt-1">
                          Enter the details to create a new {entityNameSingular}
                          .
                        </p>
                      </div>
                      <div className="flex flex-1 flex-col px-4 sm:px-6 space-y-6 pb-5 pt-6">
                        <div>
                          <label className="block text-sm font-medium text-gray-900 mb-1">
                            Name
                          </label>
                          <input
                            className="block w-full rounded-md border-0 p-2 bg-gray-100 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm"
                            disabled={isCreating}
                            onChange={(e) => setName(e.target.value)}
                            type="text"
                            value={name}
                          />
                        </div>
                      </div>
                    </div>
                    <div className="flex flex-col space-y-3 px-4 py-4">
                      <div className="flex space-x-4">
                        <Button
                          className="flex-1 justify-center"
                          disabled={isCreating}
                          onClick={handleClose}
                          variant="outline"
                        >
                          Cancel
                        </Button>
                        <Button
                          className="flex-1 justify-center bg-primary text-white hover:bg-primary-dark"
                          disabled={isCreating || !name.trim()}
                          onClick={handleCreate}
                        >
                          {isCreating
                            ? `Creating ${entityNameSingular}...`
                            : "Create"}
                        </Button>
                      </div>
                    </div>
                  </div>
                </DialogPanel>
              </Transition.Child>
            </div>
          </div>
        </div>
      </Dialog>
    </Transition.Root>
  );
}
