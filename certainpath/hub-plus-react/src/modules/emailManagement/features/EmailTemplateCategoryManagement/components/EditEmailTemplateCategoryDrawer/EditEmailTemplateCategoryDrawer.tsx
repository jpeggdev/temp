import React, { Fragment, useEffect, useState } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { useAppDispatch, useAppSelector } from "@/app/hooks";
import { RootState } from "@/app/rootReducer";
import { useNotification } from "@/context/NotificationContext";
import EmailTemplateCategoryForm, {
  EmailTemplateCategoryFormValues,
  ColorItem,
} from "../EmailTemplateCategoryForm/EmailTemplateCategoryForm";
import EmailTemplateCategoryFormLoadingSkeleton from "../EmailTemplateCategoryFormLoadingSkeleton/EmailTemplateCategoryFormLoadingSkeleton";
import {
  getEditEmailTemplateCategoryAction,
  updateEmailTemplateCategoryAction,
} from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/slice/emailTemplateCategorySlice";
import { getCreateUpdateEmailTemplateCategoryMetadata } from "@/modules/emailManagement/features/EmailTemplateCategoryManagement/api/getCreateUpdateEmailTemplateCategoryMetadata/getCreateUpdateEmailTemplateCategoryMetadataApi";

interface EditEmailTemplateCategoryDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  categoryId: number;
  onSuccess?: () => void;
}

const EditEmailTemplateCategoryDrawer: React.FC<
  EditEmailTemplateCategoryDrawerProps
> = ({ isOpen, onClose, categoryId, onSuccess }) => {
  const dispatch = useAppDispatch();
  const { showNotification } = useNotification();
  const { loadingGet, getError, fetchedCategory, loadingUpdate } =
    useAppSelector((state: RootState) => state.emailTemplateCategory);
  const [availableColors, setAvailableColors] = useState<ColorItem[]>([]);

  useEffect(() => {
    if (isOpen) {
      dispatch(getEditEmailTemplateCategoryAction(categoryId));

      getCreateUpdateEmailTemplateCategoryMetadata()
        .then((res) => {
          setAvailableColors(res.data.colors ?? []);
        })
        .catch((err) => {
          console.error("Failed to fetch color metadata:", err);
        });
    }
  }, [isOpen, categoryId, dispatch]);

  const handleSubmit = (values: EmailTemplateCategoryFormValues) => {
    dispatch(
      updateEmailTemplateCategoryAction(categoryId, values, () => {
        showNotification?.(
          "Success",
          "Email Template Category updated successfully.",
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
                        Edit Email Template Category
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
                      {getError && (
                        <p className="text-sm text-red-500 mb-2">{getError}</p>
                      )}
                      {loadingGet ? (
                        <EmailTemplateCategoryFormLoadingSkeleton />
                      ) : fetchedCategory ? (
                        <EmailTemplateCategoryForm
                          availableColors={availableColors}
                          initialData={{
                            name: fetchedCategory.name ?? "",
                            displayedName: fetchedCategory.displayedName ?? "",
                            description: fetchedCategory.description ?? "",
                            colorId: fetchedCategory.colorId ?? 1,
                          }}
                          loading={loadingUpdate}
                          onSubmit={handleSubmit}
                        />
                      ) : (
                        <p className="text-sm text-gray-500">
                          No category found or error occurred.
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

export default EditEmailTemplateCategoryDrawer;
