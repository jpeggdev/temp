import React from "react";
import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from "@headlessui/react";

interface ConfirmationDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
  dialogTitleContent: React.ReactNode;
  dialogInstructionTitle: string;
  dialogInstructionItems: string[];
  dialogInstructionFinalQuestion: string;
  confirmMessage: string;
  cancelMessage: string;
}

const ConfirmationDialog: React.FC<ConfirmationDialogProps> = ({
  isOpen,
  onClose,
  onConfirm,
  dialogTitleContent,
  dialogInstructionTitle,
  dialogInstructionItems,
  dialogInstructionFinalQuestion,
  confirmMessage,
  cancelMessage,
}) => {
  return (
    <Dialog className="relative z-30" onClose={onClose} open={isOpen}>
      <DialogBackdrop className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      <div className="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <DialogPanel className="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
            <div>
              <div className="mt-3 text-center sm:mt-5">
                <DialogTitle
                  as="h3"
                  className="text-base font-semibold leading-6 text-gray-900"
                >
                  {dialogTitleContent}
                </DialogTitle>
                <div className="mt-4 text-sm text-gray-700 text-left">
                  <p>{dialogInstructionTitle}</p>
                  <ul className="list-disc list-inside mt-2 mb-2 space-y-1">
                    {dialogInstructionItems.map((item, index) => (
                      <li key={index}>{item}</li>
                    ))}
                  </ul>
                  <p>{dialogInstructionFinalQuestion}</p>
                </div>
              </div>
            </div>
            <div className="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
              {confirmMessage && (
                <button
                  className="inline-flex w-full justify-center rounded-md bg-red-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-600 sm:col-start-2"
                  onClick={onConfirm}
                  type="button"
                >
                  {confirmMessage}
                </button>
              )}
              <button
                className={`mt-3 inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-gray-300 ${
                  !confirmMessage
                    ? "sm:col-span-2 bg-red-500 hover:bg-red-600 text-white"
                    : "sm:col-start-1 sm:mt-0 bg-white hover:bg-gray-50 text-gray-900"
                }`}
                onClick={onClose}
                type="button"
              >
                {cancelMessage}
              </button>
            </div>
          </DialogPanel>
        </div>
      </div>
    </Dialog>
  );
};

export default ConfirmationDialog;
