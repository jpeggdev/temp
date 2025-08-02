import React, { Fragment, useState, useEffect } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { FolderPlus, X, Loader2 } from "lucide-react";
import { Input } from "@/components/ui/input";
import { FolderInfo } from "../../api/listFolderContents/types";
import styles from "./CreateFolderDialog.module.css";

interface CreateFolderDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: (folderName: string) => Promise<void> | void;
  currentFolder: FolderInfo | null;
}

const CreateFolderDialog: React.FC<CreateFolderDialogProps> = ({
  isOpen,
  onClose,
  onConfirm,
  currentFolder,
}) => {
  const [isCreating, setIsCreating] = useState<boolean>(false);
  const [folderName, setFolderName] = useState<string>("");

  // Reset state when dialog opens/closes
  useEffect(() => {
    if (!isOpen) {
      setIsCreating(false);
    } else {
      setFolderName("");
    }
  }, [isOpen]);

  const handleConfirm = async (): Promise<void> => {
    if (!folderName.trim()) return;

    setIsCreating(true);
    try {
      await onConfirm(folderName);
      onClose();
    } catch (error) {
      console.error("Error creating folder:", error);
      setIsCreating(false);
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === "Enter") {
      handleConfirm();
    }
  };

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog as="div" className={styles.dialogWrapper} onClose={onClose}>
        {/* Backdrop */}
        <Transition.Child
          as={Fragment}
          enter="ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className={styles.backdrop} />
        </Transition.Child>

        {/* Dialog positioning wrapper */}
        <div className={styles.fixedWrapper}>
          <div className={styles.dialogContainer}>
            {/* Dialog panel with animation */}
            <Transition.Child
              as={Fragment}
              enter="ease-out duration-300"
              enterFrom="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enterTo="opacity-100 translate-y-0 sm:scale-100"
              leave="ease-in duration-200"
              leaveFrom="opacity-100 translate-y-0 sm:scale-100"
              leaveTo="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
              <Dialog.Panel className={styles.dialogPanel}>
                {/* Header */}
                <div className={styles.dialogHeader}>
                  <Dialog.Title as="h3" className={styles.dialogTitle}>
                    <FolderPlus className={styles.titleIcon} size={20} />
                    Create New Folder
                  </Dialog.Title>
                  <button
                    className={styles.closeButton}
                    onClick={onClose}
                    type="button"
                  >
                    <X size={16} />
                  </button>
                </div>

                {/* Content */}
                <div className={styles.dialogContent}>
                  <div className={styles.iconContainer}>
                    <FolderPlus className={styles.folderIcon} size={48} />
                  </div>

                  <div className={styles.formWrapper}>
                    <p className={styles.folderLocation}>
                      Location:{" "}
                      <span className={styles.folderPath}>
                        {currentFolder?.name || "Root"}
                      </span>
                    </p>

                    <label className={styles.inputLabel} htmlFor="folder-name">
                      Folder Name
                    </label>

                    <Input
                      autoFocus
                      className={styles.folderInput}
                      id="folder-name"
                      onChange={(e) => setFolderName(e.target.value)}
                      onKeyDown={handleKeyDown}
                      placeholder="Enter folder name"
                      value={folderName}
                    />
                  </div>
                </div>

                {/* Footer */}
                <div className={styles.buttonGroup}>
                  <button
                    className={styles.cancelButton}
                    disabled={isCreating}
                    onClick={onClose}
                    type="button"
                  >
                    Cancel
                  </button>

                  <button
                    className={`${styles.createButton} ${
                      isCreating || !folderName.trim()
                        ? styles.createButtonDisabled
                        : ""
                    }`}
                    disabled={isCreating || !folderName.trim()}
                    onClick={handleConfirm}
                    type="button"
                  >
                    {isCreating ? (
                      <>
                        <Loader2
                          className={`${styles.buttonIcon} ${styles.spinningIcon}`}
                          size={16}
                        />
                        Creating...
                      </>
                    ) : (
                      <>
                        <FolderPlus className={styles.buttonIcon} size={16} />
                        Create Folder
                      </>
                    )}
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

export default CreateFolderDialog;
