import React, { Fragment, useState, useEffect } from "react";
import { Dialog, Transition } from "@headlessui/react";
import {
  AlertTriangle,
  X,
  Trash2,
  Loader2,
  CheckCircle,
  XCircle,
} from "lucide-react";
import { FileDeleteJob } from "../../graphql/subscriptions/onFileDeleteJob/types";
import styles from "./BulkDeleteDialog.module.css";

interface BulkDeleteDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => Promise<void>;
  itemCount: number;
  deleteJob: FileDeleteJob | null;
}

const BulkDeleteDialog: React.FC<BulkDeleteDialogProps> = ({
  isOpen,
  onClose,
  onConfirm,
  itemCount,
  deleteJob,
}) => {
  const [isDeleting, setIsDeleting] = useState<boolean>(false);
  const [showFailedItems, setShowFailedItems] = useState<boolean>(false);

  // Reset states when modal closes
  useEffect(() => {
    if (!isOpen) {
      setIsDeleting(false);
      setShowFailedItems(false);
    }
  }, [isOpen]);

  const handleConfirm = async (): Promise<void> => {
    setIsDeleting(true);
    try {
      await onConfirm();
      // Note: We don't close the dialog here as we want to show progress
    } catch (error) {
      console.error("Error starting bulk delete:", error);
      setIsDeleting(false);
    }
  };

  const isCompleted = deleteJob?.status === "completed";
  const hasFailedItems =
    deleteJob?.failed_items && Object.keys(deleteJob.failed_items).length > 0;

  const renderProgressBar = () => {
    if (!deleteJob) return null;

    const percent = parseFloat(deleteJob.progress_percent);

    return (
      <div className={styles.progressBarContainer}>
        <div className={styles.progressBar} style={{ width: `${percent}%` }} />
      </div>
    );
  };

  const renderStatusIcon = () => {
    if (!deleteJob) return null;

    if (deleteJob.status === "processing") {
      return (
        <Loader2
          className={`${styles.statusIcon} ${styles.spinningIcon}`}
          size={48}
        />
      );
    } else if (deleteJob.status === "completed") {
      if (hasFailedItems) {
        return (
          <XCircle
            className={`${styles.statusIcon} ${styles.errorIcon}`}
            size={48}
          />
        );
      } else {
        return (
          <CheckCircle
            className={`${styles.statusIcon} ${styles.successIcon}`}
            size={48}
          />
        );
      }
    } else {
      return <AlertTriangle className={styles.warningIcon} size={48} />;
    }
  };

  const renderStatusText = () => {
    if (!deleteJob) {
      return "Confirm deletion of files and folders";
    }

    if (deleteJob.status === "pending") {
      return "Preparing to delete files...";
    } else if (deleteJob.status === "processing") {
      return "Deleting files...";
    } else if (deleteJob.status === "completed") {
      if (hasFailedItems) {
        const failedCount = Object.keys(deleteJob.failed_items || {}).length;
        return `Deletion completed with issues: ${failedCount} item${failedCount > 1 ? "s" : ""} could not be deleted`;
      } else {
        return "Deletion completed successfully!";
      }
    } else {
      return "Unknown status";
    }
  };

  const renderFailedItems = () => {
    if (
      !deleteJob?.failed_items ||
      Object.keys(deleteJob.failed_items).length === 0
    ) {
      return null;
    }

    return (
      <div className={styles.failedItemsContainer}>
        <button
          className={styles.toggleFailedItemsButton}
          onClick={() => setShowFailedItems(!showFailedItems)}
        >
          {showFailedItems ? "Hide" : "Show"} failed items (
          {Object.keys(deleteJob.failed_items).length})
        </button>

        {showFailedItems && (
          <div className={styles.failedItemsList}>
            {Object.entries(deleteJob.failed_items).map(([uuid, reason]) => (
              <div className={styles.failedItem} key={uuid}>
                <XCircle className={styles.failedItemIcon} size={16} />
                <div className={styles.failedItemDetails}>
                  <div className={styles.failedItemUuid}>{uuid}</div>
                  <div className={styles.failedItemReason}>{reason}</div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    );
  };

  return (
    <Transition.Root as={Fragment} show={isOpen}>
      <Dialog
        as="div"
        className={styles.dialogWrapper}
        onClose={isCompleted ? onClose : () => {}}
      >
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
                    Bulk Delete
                  </Dialog.Title>
                  {isCompleted && (
                    <button
                      className={styles.closeButton}
                      onClick={onClose}
                      type="button"
                    >
                      <X size={16} />
                    </button>
                  )}
                </div>

                {/* Content */}
                <div className={styles.dialogContent}>
                  {renderStatusIcon()}

                  <div className={styles.statusTextWrapper}>
                    <h4 className={styles.statusTitle}>{renderStatusText()}</h4>

                    {!deleteJob && (
                      <p className={styles.warningText}>
                        Are you sure you want to delete{" "}
                        <strong>
                          {itemCount} item{itemCount !== 1 ? "s" : ""}
                        </strong>
                        ? This action <strong>cannot be undone</strong>.
                      </p>
                    )}

                    {deleteJob && (
                      <div className={styles.progressStats}>
                        <div className={styles.statItem}>
                          <span className={styles.statLabel}>Total:</span>
                          <span className={styles.statValue}>
                            {deleteJob.total_files}
                          </span>
                        </div>
                        <div className={styles.statItem}>
                          <span className={styles.statLabel}>Processed:</span>
                          <span className={styles.statValue}>
                            {deleteJob.processed_files}
                          </span>
                        </div>
                        <div className={styles.statItem}>
                          <span className={styles.statLabel}>Deleted:</span>
                          <span className={styles.statValue}>
                            {deleteJob.successful_deletes}
                          </span>
                        </div>
                      </div>
                    )}

                    {deleteJob && renderProgressBar()}

                    {hasFailedItems && renderFailedItems()}
                  </div>
                </div>

                {/* Footer */}
                <div className={styles.buttonGroup}>
                  {!isDeleting && !deleteJob && (
                    <button
                      className={styles.cancelButton}
                      onClick={onClose}
                      type="button"
                    >
                      Cancel
                    </button>
                  )}

                  {isCompleted ? (
                    <button
                      className={styles.doneButton}
                      onClick={onClose}
                      type="button"
                    >
                      Done
                    </button>
                  ) : !deleteJob ? (
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
                          Delete {itemCount} item{itemCount !== 1 ? "s" : ""}
                        </>
                      )}
                    </button>
                  ) : null}
                </div>
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </div>
      </Dialog>
    </Transition.Root>
  );
};

export default BulkDeleteDialog;
