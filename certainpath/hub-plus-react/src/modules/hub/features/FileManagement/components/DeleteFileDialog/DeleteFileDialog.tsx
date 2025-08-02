import React, { Fragment, useState, useEffect } from "react";
import { Dialog, Transition } from "@headlessui/react";
import {
  AlertTriangle,
  X,
  Trash2,
  Folder,
  File,
  FileText,
  Loader2,
} from "lucide-react";
import { FilesystemNode } from "../../api/listFolderContents/types";
import { formatFileSize } from "../../utils/formatters";
import styles from "./DeleteFileDialog.module.css";

interface DeleteFileDialogProps {
  file: FilesystemNode | null;
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => Promise<void> | void;
}

const DeleteFileDialog: React.FC<DeleteFileDialogProps> = ({
  file,
  isOpen,
  onClose,
  onConfirm,
}) => {
  const [isDeleting, setIsDeleting] = useState<boolean>(false);

  // Reset deleting state when modal closes
  useEffect(() => {
    if (!isOpen) {
      setIsDeleting(false);
    }
  }, [isOpen]);

  const handleConfirm = async (): Promise<void> => {
    if (!file) return;

    setIsDeleting(true);
    try {
      await onConfirm();
      onClose();
    } catch (error) {
      console.error("Error deleting file:", error);
      setIsDeleting(false);
    }
  };

  if (!file) return null;

  const getFileIcon = () => {
    if (file.type === "folder")
      return <Folder className={styles.folderIcon} size={48} />;

    if (!file.mimeType) return <File className={styles.fileIcon} size={48} />;

    if (
      file.mimeType.startsWith("image/") ||
      file.mimeType.startsWith("video/") ||
      file.mimeType.startsWith("audio/")
    ) {
      return <FileText className={styles.fileIcon} size={48} />;
    }

    return <File className={styles.fileIcon} size={48} />;
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
                    <Trash2 className={styles.titleIcon} size={20} />
                    Delete {file.type === "folder" ? "Folder" : "File"}
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
                  <div className={styles.warningIcon}>
                    <AlertTriangle size={48} />
                  </div>

                  <div className={styles.warningTextWrapper}>
                    <p className={styles.warningText}>
                      Are you sure you want to delete{" "}
                      <strong>"{file.name}"</strong>? This action{" "}
                      <strong>cannot be undone</strong>.
                    </p>

                    {/* File details card */}
                    <div className={styles.fileDetailsCard}>
                      <div className={styles.fileDetailsHeader}>
                        {getFileIcon()}
                        <div className={styles.fileNameContainer}>
                          <h4 className={styles.fileName}>{file.name}</h4>
                          <span className={styles.fileType}>
                            {file.type === "folder"
                              ? "Folder"
                              : file.fileType || "File"}
                          </span>
                        </div>
                      </div>

                      <div className={styles.fileDetailsContent}>
                        {file.type === "file" &&
                          file.fileSize !== undefined && (
                            <div className={styles.fileDetail}>
                              <span className={styles.detailLabel}>Size:</span>
                              <span className={styles.detailValue}>
                                {formatFileSize(file.fileSize || 0)}
                              </span>
                            </div>
                          )}

                        {file.createdAt && (
                          <div className={styles.fileDetail}>
                            <span className={styles.detailLabel}>Created:</span>
                            <span className={styles.detailValue}>
                              {new Date(file.createdAt).toLocaleDateString()}
                            </span>
                          </div>
                        )}
                      </div>
                    </div>

                    {file.type === "folder" && (
                      <div className={styles.folderWarning}>
                        <AlertTriangle
                          className={styles.folderWarningIcon}
                          size={16}
                        />
                        <p>
                          You can only delete empty folders. Make sure this
                          folder does not contain any files or subfolders.
                        </p>
                      </div>
                    )}
                  </div>
                </div>

                {/* Footer */}
                <div className={styles.buttonGroup}>
                  <button
                    className={styles.cancelButton}
                    disabled={isDeleting}
                    onClick={onClose}
                    type="button"
                  >
                    Cancel
                  </button>

                  <button
                    className={`${styles.deleteButton} ${
                      isDeleting ? styles.deleteButtonDisabled : ""
                    }`}
                    disabled={isDeleting}
                    onClick={handleConfirm}
                    type="button"
                  >
                    {isDeleting ? (
                      <>
                        <Loader2
                          className={`${styles.buttonIcon} ${styles.spinningIcon}`}
                          size={16}
                        />
                        Deleting...
                      </>
                    ) : (
                      <>
                        <Trash2 className={styles.buttonIcon} size={16} />
                        Delete {file.type === "folder" ? "Folder" : "File"}
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

export default DeleteFileDialog;
